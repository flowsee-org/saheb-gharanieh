/**
 * Saheb Gharaniyeh Cafe — menu interactions.
 *
 *  · preloader veil                     · scroll-spy for the sticky section bar
 *  · smooth in-page navigation          · reading-progress rule
 *  · reveal-on-scroll                   · back-to-top button
 *  · dark / light theme switch
 */

const root = document.documentElement;
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ------------------------------------------------------------------ */
/*  Theme switch                                                       */
/* ------------------------------------------------------------------ */

/*
 * The inline script in the layout has already painted the right palette; this
 * only keeps the button, the address-bar colour and localStorage in step. Dark
 * is the house theme, so anything other than an explicit "light" means dark.
 */
const THEME_KEY = 'sg-theme';
const THEME_COLORS = { dark: '#050404', light: '#faf4e8' };
const themeToggle = document.getElementById('theme-toggle');

function applyTheme(theme) {
    root.dataset.theme = theme;

    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', THEME_COLORS[theme]);

    themeToggle?.setAttribute('aria-pressed', theme === 'light' ? 'true' : 'false');
    themeToggle?.setAttribute('title', theme === 'light' ? 'پوسته تاریک' : 'پوسته روشن');
}

applyTheme(root.dataset.theme === 'light' ? 'light' : 'dark');

themeToggle?.addEventListener('click', () => {
    const next = root.dataset.theme === 'light' ? 'dark' : 'light';

    applyTheme(next);

    try {
        localStorage.setItem(THEME_KEY, next);
    } catch (e) {
        /* private mode: the choice simply lasts for this page */
    }
});

/* ------------------------------------------------------------------ */
/*  Preloader                                                          */
/* ------------------------------------------------------------------ */

function revealPage() {
    if (root.dataset.loaded === 'true') return;
    root.dataset.loaded = 'true';
    document.getElementById('preloader')?.setAttribute('aria-hidden', 'true');
}

window.addEventListener('load', () => setTimeout(revealPage, 260));
// Never let a slow asset trap the visitor behind the veil.
setTimeout(revealPage, 2600);

/* ------------------------------------------------------------------ */
/*  Fade photos in once decoded                                        */
/* ------------------------------------------------------------------ */

document.querySelectorAll('img[data-fade-in]').forEach((img) => {
    if (img.complete) {
        img.classList.add('is-loaded');
        return;
    }
    img.addEventListener('load', () => img.classList.add('is-loaded'), { once: true });
});

/* ------------------------------------------------------------------ */
/*  Reveal on scroll                                                   */
/* ------------------------------------------------------------------ */

const revealables = document.querySelectorAll('.reveal');

if (prefersReducedMotion || !('IntersectionObserver' in window)) {
    revealables.forEach((el) => el.classList.add('is-visible'));
} else {
    const revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.06 }
    );

    revealables.forEach((el) => revealObserver.observe(el));
}

/* ------------------------------------------------------------------ */
/*  Back to top                                                        */
/* ------------------------------------------------------------------ */

const toTop = document.getElementById('to-top');

toTop?.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
});

/* ------------------------------------------------------------------ */
/*  Menu page: sticky section bar                                      */
/* ------------------------------------------------------------------ */

const menuRoot = document.getElementById('menu-root');
const topbar = document.getElementById('topbar');
const flag = document.getElementById('section-flag');
const flagText = document.getElementById('section-flag-text');
const progress = document.getElementById('topbar-progress');
const chipsNav = document.getElementById('section-chips');
const sections = Array.from(document.querySelectorAll('.scroll-section'));

/** Height of the sticky bar — the line at which a section counts as "current". */
function barHeight() {
    return topbar ? topbar.getBoundingClientRect().height : 0;
}

/** Scroll a section under the sticky bar, honouring reduced-motion. */
function scrollToSection(slug, behavior) {
    const target = document.getElementById(slug);
    if (!target) return;

    const top = target.getBoundingClientRect().top + window.scrollY - barHeight() - 12;

    window.scrollTo({
        top: Math.max(top, 0),
        behavior: behavior ?? (prefersReducedMotion ? 'auto' : 'smooth'),
    });
}

/** Keep the active chip visible inside its horizontal scroller. */
function centerChip(chip) {
    if (!chipsNav || !chip) return;

    // scrollIntoView lets the browser resolve RTL scrolling (including the
    // no-scroll-room-at-the-end case) instead of hand-rolling scrollLeft math.
    const behavior = prefersReducedMotion ? 'auto' : 'smooth';
    chip.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior });
}

let currentSlug = null;

function setCurrentSection(slug) {
    if (!slug || slug === currentSlug) return;
    currentSlug = slug;

    const section = document.getElementById(slug);

    if (flagText && section) {
        flagText.textContent = section.dataset.sectionName ?? slug;

        // restart the fade animation
        flag?.setAttribute('data-swap', 'false');
        void flag?.offsetWidth;
        flag?.setAttribute('data-swap', 'true');
    }

    chipsNav?.querySelectorAll('[data-chip]').forEach((chip) => {
        const isActive = chip.dataset.chip === slug;
        chip.setAttribute('aria-current', isActive ? 'true' : 'false');
        if (isActive) centerChip(chip);
    });

    if (history.replaceState) {
        history.replaceState(null, '', `#${slug}`);
    }
}

/**
 * The current section is the last one whose top edge has passed the sticky bar.
 * Reading positions rather than intersection ratios keeps short sections and
 * fast flicks from flipping the label to the wrong heading.
 */
function syncSection() {
    if (!sections.length) return;

    const line = barHeight() + 24;
    let active = sections[0];

    for (const section of sections) {
        if (section.getBoundingClientRect().top <= line) {
            active = section;
        } else {
            break;
        }
    }

    // At the very bottom the final section is the one being read.
    const atBottom = window.innerHeight + window.scrollY >= document.body.scrollHeight - 2;
    if (atBottom) {
        active = sections[sections.length - 1];
    }

    setCurrentSection(active.dataset.section);
}

function syncProgress() {
    if (!progress) return;

    const scrollable = document.documentElement.scrollHeight - window.innerHeight;
    const ratio = scrollable > 0 ? Math.min(window.scrollY / scrollable, 1) : 0;

    progress.style.width = `${(ratio * 100).toFixed(2)}%`;
}

function onScroll() {
    topbar?.setAttribute('data-scrolled', window.scrollY > 8 ? 'true' : 'false');
    toTop?.setAttribute('data-visible', window.scrollY > 420 ? 'true' : 'false');
    syncSection();
    syncProgress();
}

if (menuRoot) {
    let ticking = false;

    window.addEventListener(
        'scroll',
        () => {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(() => {
                onScroll();
                ticking = false;
            });
        },
        { passive: true }
    );

    window.addEventListener('resize', () => {
        syncSection();
        syncProgress();
    });

    // Chip taps and any in-page anchor scroll smoothly under the sticky bar.
    document.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const slug = link.getAttribute('href')?.slice(1);
            if (!slug || !document.getElementById(slug)) return;

            event.preventDefault();
            scrollToSection(slug);
            setCurrentSection(slug);
        });
    });

    // Arriving from a landing card (/menu/hot-drinks#hot-drinks): re-align the
    // native anchor jump so the heading clears the sticky bar.
    const initial = menuRoot.dataset.initialSection || window.location.hash.slice(1);

    if (initial && document.getElementById(initial)) {
        let userMoved = false;
        const markMoved = () => {
            userMoved = true;
        };

        ['wheel', 'touchstart', 'keydown'].forEach((type) =>
            window.addEventListener(type, markMoved, { once: true, passive: true })
        );

        const align = () => {
            if (userMoved) return;
            scrollToSection(initial, 'auto');
            setCurrentSection(initial);
            syncProgress();
        };

        align();
        // Fonts and late layout shifts can move the anchor; re-align once.
        window.addEventListener('load', () => setTimeout(align, 80), { once: true });
    }

    onScroll();
} else {
    // Landing page still wants the back-to-top button.
    window.addEventListener(
        'scroll',
        () => toTop?.setAttribute('data-visible', window.scrollY > 420 ? 'true' : 'false'),
        { passive: true }
    );
}
