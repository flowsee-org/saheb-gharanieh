#!/usr/bin/env node
/**
 * Minimal CDP driver: loads a URL in headless Chrome, waits for the page to
 * settle, evaluates a diagnostic snippet and writes a full-page screenshot.
 *
 *   node tools/shot.mjs <url> <out.png> [width] [height] [--full] [--eval=<js>]
 *   node tools/shot.mjs --url=… --out=… [--width=390] [--height=844] [--full] …
 *
 * `--login=admin:admin123` signs into /wp-admin first, so the panel's pages can
 * be captured: the browser profile is fresh every run and the session cookie is
 * httpOnly, so there is no other way to arrive at one logged in.
 */
import { spawn } from 'node:child_process';
import { writeFileSync, mkdtempSync, mkdirSync } from 'node:fs';
import { tmpdir, } from 'node:os';
import { dirname, join } from 'node:path';

const argv = process.argv.slice(2);
const rest = argv.filter((a) => a.startsWith('--'));
const positional = argv.filter((a) => !a.startsWith('--'));
const flag = (name) => rest.find((a) => a.startsWith(`--${name}=`))?.slice(name.length + 3);

const url = flag('url') ?? positional[0];
const out = flag('out') ?? positional[1];
const width = flag('width') ?? positional[2] ?? '390';
const height = flag('height') ?? positional[3] ?? '844';
const full = rest.includes('--full');
const evalArg = flag('eval');
const port = 9333 + Math.floor(process.uptime() * 1000) % 500;

const profile = mkdtempSync(join(tmpdir(), 'cdp-'));
const chrome = spawn('google-chrome', [
    '--headless=new',
    '--disable-gpu',
    '--no-sandbox',
    '--hide-scrollbars',
    '--force-device-scale-factor=2',
    `--remote-debugging-port=${port}`,
    `--user-data-dir=${profile}`,
    `--window-size=1280,900`,
    'about:blank',
], { stdio: 'ignore' });

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function target() {
    for (let i = 0; i < 60; i++) {
        try {
            const res = await fetch(`http://127.0.0.1:${port}/json/version`);
            return (await res.json()).webSocketDebuggerUrl;
        } catch {
            await sleep(150);
        }
    }
    throw new Error('chrome did not start');
}

const ws = new WebSocket(await target());
await new Promise((r) => ws.addEventListener('open', r, { once: true }));

let id = 0;
const pending = new Map();
const events = [];

ws.addEventListener('message', (event) => {
    const msg = JSON.parse(event.data);
    if (msg.id && pending.has(msg.id)) {
        const { resolve, reject } = pending.get(msg.id);
        pending.delete(msg.id);
        msg.error ? reject(new Error(JSON.stringify(msg.error))) : resolve(msg.result);
    } else if (msg.method) {
        events.push(msg);
    }
});

function send(method, params = {}, sessionId) {
    const message = { id: ++id, method, params };
    if (sessionId) message.sessionId = sessionId;
    return new Promise((resolve, reject) => {
        pending.set(message.id, { resolve, reject });
        ws.send(JSON.stringify(message));
    });
}

// Attach to a fresh tab.
const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });
const call = (method, params) => send(method, params, sessionId);

await call('Page.enable');
await call('Runtime.enable');
await call('Log.enable');
// Emulate the phone inside a roomy window (headless Chrome clamps small windows).
await call('Emulation.setDeviceMetricsOverride', {
    width: Number(width),
    height: Number(height),
    deviceScaleFactor: 2,
    mobile: true,
    screenWidth: Number(width),
    screenHeight: Number(height),
    positionX: 0,
    positionY: 0,
});
await call('Emulation.setTouchEmulationEnabled', { enabled: true });

// Sign in before the page we actually want, when asked. The form is filled and
// submitted in the page so it carries the CSRF token that was printed into it.
const login = flag('login');

if (login) {
    const [username, password] = login.split(':');

    await call('Page.navigate', { url: `${new URL(url).origin}/wp-admin/login` });
    await sleep(1800);

    await call('Runtime.evaluate', {
        expression: `(() => {
            const form = document.querySelector('.admin-login-form');
            if (!form) return 'no form';
            form.querySelector('[name=username]').value = ${JSON.stringify(username)};
            form.querySelector('[name=password]').value = ${JSON.stringify(password)};
            form.submit();
            return 'submitted';
        })()`,
        returnByValue: true,
    });
    await sleep(2200);
}

await call('Page.navigate', { url });
await sleep(2500);

const consoleErrors = events
    .filter((e) => e.method === 'Log.entryAdded' && e.params.entry.level === 'error')
    // The url is what makes a network entry actionable — "failed to load
    // resource" on its own says nothing about which one.
    .map((e) => [e.params.entry.source + ':', e.params.entry.text, e.params.entry.url].filter(Boolean).join(' '));

const script = evalArg ?? `JSON.stringify({
    innerWidth: window.innerWidth,
    docScrollWidth: document.documentElement.scrollWidth,
    bodyScrollWidth: document.body.scrollWidth,
    docScrollHeight: document.documentElement.scrollHeight,
    loaded: document.documentElement.dataset.loaded ?? null,
    widest: [...document.querySelectorAll('body *')]
        .map((el) => ({ t: el.tagName + '.' + (el.className?.toString?.().slice(0, 60) ?? ''), r: Math.round(el.getBoundingClientRect().right), l: Math.round(el.getBoundingClientRect().left), w: Math.round(el.getBoundingClientRect().width) }))
        .filter((e) => e.r > window.innerWidth + 2 || e.l < -2)
        .slice(0, 12),
})`;

const evaluated = await call('Runtime.evaluate', { expression: script, returnByValue: true, awaitPromise: true });

// Scroll-reveal keeps below-fold content invisible; force it on for full captures.
if (rest.includes('--reveal')) {
    await call('Runtime.evaluate', {
        expression: `document.querySelectorAll('.reveal').forEach(el => el.classList.add('is-visible')); 'ok'`,
        returnByValue: true,
    });
    await sleep(900);
}

const scrollArg = flag('scroll');

if (scrollArg) {
    const expr = /^\d+$/.test(scrollArg)
        ? `window.scrollTo({top: ${scrollArg}, behavior: 'instant'})`
        : `document.querySelector('${scrollArg}')?.scrollIntoView({block: 'start', behavior: 'instant'})`;

    await call('Runtime.evaluate', { expression: `${expr}; 'ok'`, returnByValue: true });
    await sleep(700);

    const after = await call('Runtime.evaluate', {
        expression: `JSON.stringify({scrollY: Math.round(window.scrollY), flag: document.getElementById('section-flag-text')?.textContent.trim()})`,
        returnByValue: true,
    });
    console.log('--- after scroll ---');
    console.log(after.result?.value);
}

console.log('--- eval ---');
console.log(evaluated.result?.value ?? JSON.stringify(evaluated));
if (consoleErrors.length) {
    console.log('--- console errors ---');
    consoleErrors.forEach((e) => console.log(e));
}

if (out && out !== '-') {
    const shot = await call('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: full,
        ...(full ? { optimizeForSpeed: false } : {}),
    });
    mkdirSync(dirname(out), { recursive: true });
    writeFileSync(out, Buffer.from(shot.data, 'base64'));
    console.log(`--- wrote ${out} ---`);
}

ws.close();
chrome.kill();
process.exit(0);
