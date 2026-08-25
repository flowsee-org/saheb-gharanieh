# کافه صاحبقرانیه — Saheb Gharaniyeh Cafe

A mobile-first digital menu for a traditional Persian café. Deep navy and brand blue, ruled
rather than framed, full RTL, Persian typography — with the menu itself transcribed from the
café's printed four-panel card.

No cart, no checkout, no payments: it is a menu you read on a phone at the table.

---

## Getting started

```bash
composer install
npm install

cp .env.example .env         # already done in this checkout
php artisan key:generate

php artisan migrate --seed   # schema + the real menu from the printed reference
npm run build                # or: npm run dev

php artisan serve
```

Run the test suite with `php artisan test`.

The database is SQLite (`database/database.sqlite`) out of the box; nothing else is required.

### Updating an install that already has data

```bash
git pull
composer install --no-dev
npm ci && npm run build
php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear && php artisan optimize
```

No seeders. This is what `.github/workflows/ci-cd.yml` runs on the server, and it
keeps everything entered in the panel — prices, uploaded photos, edited copy.

**Never run `migrate:fresh` on an install with real data.** It drops every table,
which takes every price and every `image_path` with it; the uploaded files are left
orphaned in `storage/app/public` with nothing pointing at them. The seeders are safe
to re-run (they only create missing rows, never overwrite an existing one), but
`migrate:fresh` empties the tables before they get the chance.

A new setting the panel must expose therefore ships as a migration, not a seeder
line — see `2026_08_25_000001_add_navigation_link_settings.php`.

---

## Pages

| Route | Name | What it does |
| --- | --- | --- |
| `/` | `home` | Three large cards (گرم / سرد / قلیان) right under the hero, café intro below them. Each card links into the menu. |
| `/menu` | `menu` | The whole menu: one ornate panel per section, sticky bar showing the section in view. |
| `/menu/{section}` | `menu.section` | Same page, auto-scrolled to that section on load (e.g. `/menu/hookah-deluxe`). |

Both routes are single-action controllers (`HomeController`, `MenuController`). An unknown
`{section}` is ignored rather than 404-ing, so a stale link still shows the full menu.

### Themes

Dark is the house theme. The switch is the round button pinned to the **top-right corner**
of every page (`resources/views/components/theme-toggle.blade.php`), and light is the
opt-in: the same menu on white, with the blues darkened to hold their contrast on paper.

- The palette is chosen in `<head>` by a short inline script — before the first paint, so
  no page ever flashes the wrong theme — and remembered in `localStorage` under `sg-theme`.
  Anything other than an explicit `light` means dark, including a first visit.
- `resources/js/app.js` only keeps the button (`aria-pressed`, `title`),
  `<meta name="theme-color">` and `localStorage` in step after a tap.
- The light theme lives in one block at the bottom of `resources/css/app.css` under
  `html[data-theme='light']`. It re-points the palette tokens — so every Tailwind utility
  in Blade (`text-cream-dim`, `bg-gold-900/30` …) follows along without touching a view —
  and then restates only the hard-coded colours that were tuned to glow on black. Those
  rules sit outside `@layer`, so they win over the component layer without `!important`.
- Ramp semantics are preserved: a low gold index still means "for headings". That ramp is
  legacy on the public pages, though — it still drives the panel and the `gold-*` utilities,
  while the menu's own colours come from the `--sg-*` tokens in `theme-overrides.css`, which
  are declared after `app.css` and win. `.gold-text` and `.gold-line` are re-pointed there to
  the house blue, so a view that still asks for gold gets the current design.

### Sticky section bar

`resources/js/app.js` runs a rAF-throttled scroll spy: the current section is the last one
whose top edge has passed under the bar. It swaps the label in `#section-flag-text`, marks
the matching chip with `aria-current="true"`, scrolls that chip into view, updates the URL
hash with `history.replaceState`, and drives the thin progress rule. Anchor clicks are
intercepted so the sticky bar height is subtracted from the scroll target.

---

## Data model

Everything on both pages comes from the database — no menu copy is hard-coded in Blade.

**`categories`** — one row per menu section.

| Column | Purpose |
| --- | --- |
| `slug` | Anchor id on the menu page and the `{section}` route segment |
| `name`, `short_name`, `latin_name`, `subtitle`, `description` | Section copy (`short_name` is the chip label) |
| `kind` | `drink` \| `hookah` — `App\Enums\CategoryKind` |
| `layout` | `grid` \| `list` — `App\Enums\CategoryLayout`; drinks use the card grid, hookah uses flavour rows |
| `icon`, `image_path` | Small glyph next to the title; optional section image |
| `price`, `price_note` | Legacy: the hookah panels used to print one service price for the whole section. They price per flavour now, so nothing reads these and the panel no longer offers them — the columns stay only so existing rows keep whatever was typed |
| `card_order`, `card_title`, `card_subtitle`, `card_latin` | Landing-page card. `card_order = NULL` means "not on the landing page" |
| `sort_order`, `is_active` | Ordering and visibility |

**`products`** — one row per item. `price` is nullable on purpose: the printed menu leaves
`قیمت :` blank, so an empty price prints «قیمت در محل» where the number would go — including
on both hookah sections, which price per flavour now. `image_path` is nullable too — when it
is empty the card shows the plain blue well (`--sg-media`) rather than a photo.
`is_active` hides an item, `is_available` renders it as "موقتاً تمام شد".

**`category_features`** — the extras strip under the Super Deluxe hookah panel
(چای زغالی، میوه فصل، باقلوا …).

**`settings`** — editable site copy (café name, tagline, intro paragraph, hours, address,
phone, Instagram, Balad and Neshan map links) as `key`/`value` rows. Read through
`Setting::map()`, which is cached and busted automatically on save/delete.

Seeders hold the real menu transcribed from the reference photo: 15 hot drinks, 18 cold
drinks and 16 hookah flavours (seeded into both hookah services), plus 8 deluxe extras.
They **create only** — an existing row is the owner's, edited in the panel, and re-running a
seeder must not overwrite it. `tests/Feature/MenuSeederTest.php` locks those counts in.
The trade is that correcting a transcription typo here no longer reaches an install that
already has the row; correct it in the panel instead.

---

## The café panel

Everything the owner edits lives under **`/wp-admin`**, behind its own `admin` auth guard
(`admin_users` table) — completely separate from the site's `web` guard. Default login from
`AdminUserSeeder` is `admin` / `admin123`; **change it before going live** (see
[Deploy](#deploy)).

| Page | What it does |
| --- | --- |
| `/wp-admin` | Dashboard: counts, the four things most likely to need attention, recent items |
| `/wp-admin/items` | Menu items — search, filter by section/status, bulk actions, inline price editing, ↑/↓ ordering |
| `/wp-admin/items/create`, `…/{id}/edit` | Full item form, including image upload and glyph picker |
| `/wp-admin/categories` | Sections — drag to reorder, toggle, and manage the extras strip inline |
| `/wp-admin/categories/create`, `…/{id}/edit` | Section form: copy, kind, layout, landing-page card |
| `/wp-admin/settings` | The `settings` rows — café intro, hours, address, Instagram, map links |
| `/wp-admin/account` | Rename the account and change the password |

Two rules the panel is built on:

- **Every page works without JavaScript.** The whole panel is real forms that post and
  reload. `resources/js/admin.js` only adds convenience: the confirm dialog, self-submitting
  filters, drag-to-reorder, image preview, the bulk bar, saving a price on blur. With JS off
  the filter form keeps its «اعمال» button, the ↑/↓ buttons still reorder, and a delete still
  deletes — it just is not questioned first.
- **Authorisation is the route's job.** `AdminRequest::authorize()` returns `true` for all
  eight form requests, because `Route::middleware('auth:admin')` in `routes/web.php` wraps
  every route but the login form. `tests/Feature/AdminAuthTest.php` walks the route table and
  asserts a guest is turned away from each one, so a route added outside that group fails the
  suite instead of shipping open.

Sections and items bind on `id` in the panel (`{category:id}`) rather than on `slug`, because
the panel is where a slug gets renamed and a model must not disappear from under the form
that is editing it.

`resources/css/admin.css` styles the panel and deliberately does **not** import Tailwind: it
is loaded next to `app.css`, whose `@theme` tokens, `@utility` rules (`gold-text`,
`gold-line`, `latin`) and `.frame` it reuses, and whose `@source '../views'` already scans the
admin markup.

---

## Deploy

The panel's own defences are in place — a single generic message for both a wrong username
and a wrong password, `session()->regenerate()` on sign-in, invalidate plus token-regenerate
on sign-out, `throttle:10,1` on the login POST, the current password required before a new
one, bcrypt at 12 rounds, server-side `image|mimes:…|max:…` on uploads with Laravel
generating the stored filename, and CSRF on every form.

What is left is configuration, and all four matter:

1. **`APP_ENV=production`, `APP_DEBUG=false`.** The checked-in `.env` is a development file
   (`local` / `true`), which is right for a laptop and wrong for a public host: with debug on,
   any error becomes a full-disclosure page listing file paths, source lines and environment
   values.
2. **Change `admin` / `admin123`.** From the account page, or
   `php artisan admin:password admin` on the server.
3. **HTTPS, and `SESSION_SECURE_COOKIE=true`.** Over plain HTTP the admin session cookie
   crosses the café's shared Wi-Fi in the clear, which is the realistic attack on a site that
   takes no payments — someone on the same network reading the cookie and walking into the
   panel.
4. **Upload limits, if the web server is nginx.** `client_max_body_size` defaults to **1M** and
   answers 413 by itself, before PHP is reached — so a normal phone photo never arrives no
   matter what the panel or php.ini say. Both files nginx needs are in `deploy/nginx/`, with
   installation notes in their comments:

   ```bash
   # The 413 fix: raises the body limit to 12M, matching post_max_size in public/.user.ini.
   sudo cp deploy/nginx/upload-size.conf /etc/nginx/conf.d/
   # Denies public/.user.ini, which nginx serves as text otherwise. Needs an
   # `include /etc/nginx/snippets/deny-user-ini.conf;` line inside the server block.
   sudo cp deploy/nginx/deny-user-ini.conf /etc/nginx/snippets/

   sudo nginx -t && sudo systemctl reload nginx
   ```

   Apache needs neither: `public/.htaccess` sets the limits for mod_php and denies the file
   already. Nothing in the deploy workflow touches nginx — this is a one-off, done as root on
   the server, and it survives deployments because it lives outside the project directory.

### How large a photo may be

One number, in `App\Support\UploadLimit::WANTED_KILOBYTES` (6144). The image field's hint, the
`max:` rule on `ProductRequest` and the check in `admin.js` all read it from there, clamped
down to whatever `upload_max_filesize` and `post_max_size` will really accept — so the panel
cannot advertise a size the server then refuses, which is exactly what it used to do.

The clamp means a misconfigured server shows an honest smaller number rather than failing:
if the hint reads less than «۶ مگابایت», PHP is the reason, not the code. `public/.user.ini`
(8M / 12M) lifts it for php-fpm, `public/.htaccess` for mod_php, and nginx needs the item
above. `php artisan serve` reads neither — its limits come from the CLI `php.ini`:

```bash
php -i | grep -E 'upload_max_filesize|post_max_size'
```

Both ceilings sit deliberately **above** 6 MB. A 7 MB photo is then refused by Laravel, with a
message naming the real limit, instead of arriving broken from PHP; and a body over
`post_max_size` is discarded whole — token and text fields with it — which is why that one has
the most headroom and why `resources/views/errors/413.blade.php` exists as the last resort.

Also worth knowing: the `/wp-admin` prefix attracts constant WordPress-scanner traffic. The
throttle makes it harmless, but it will fill the logs, so do not read a stream of failed
logins as a targeted attempt.

Never commit `.env` (it holds `APP_KEY`) or `database/database.sqlite` (it holds the admin
password hash). Move the database between machines out of band — `scp`, not `git`.

---

## Front-end

- Tailwind CSS 4 via `@tailwindcss/vite`, with the design tokens (night/gold palette,
  shadows, easing) declared in `@theme` in `resources/css/app.css` and re-pointed for the
  light theme (see [Themes](#themes)).
- Self-hosted variable fonts in `public/fonts`: Vazirmatn for Persian, Montserrat for every
  latin line. Montserrat is declared twice — once as `Montserrat` for the `latin` utility, and
  once as `Montserrat Latin` restricted by `unicode-range` so it takes the latin characters out
  of body copy without touching Persian metrics. `U+0020` is left out of that range on purpose:
  a space taken from a latin face would change the word spacing of Persian text around it.
- Blade anonymous components in `resources/views/components`: `frame`, `ornament.*`,
  `icon.*`, `product-card`, `flavor-row`, `price-tag`, `logo`, `theme-toggle`,
  `site-footer`. `logo` paints `images/logo-dark.png` / `logo-light.png` as a background
  keyed on `html[data-theme]`, so only the active theme's file is fetched.
- `@fa(...)` prints Persian digits and `@price(...)` prints a Persian price with " تومان"
  (see `App\Support\Persian` and `AppServiceProvider`).
- Vanilla JS only: theme switch, preloader, IntersectionObserver reveals, image fade-in,
  scroll spy, back-to-top. `prefers-reduced-motion` is respected.
- Eight Vite entries. The public menu loads four CSS files in this order — `app.css`
  (Tailwind and the `@theme` tokens), `brand.css`, `theme-overrides.css` (the `--sg-*`
  tokens and the `.menu-*` components), `menu-redesign.css` — plus `app.js` and
  `menu-redesign.js`; the panel loads `admin.css` / `admin.js`. **Every entry named in an
  `@vite([...])` call must also be listed in `vite.config.js`.** A name that is in the Blade
  call but not the config is absent from `public/build/manifest.json`, and `@vite` then
  throws — which 500s every page using that layout, not just the styling. `php artisan test`
  leaves Vite switched on precisely so that fails the suite instead of reaching production.

### Screenshots during development

`tools/shot.mjs` drives headless Chrome over CDP for mobile-viewport captures and in-page
diagnostics (overflow check, current section flag):

```bash
node tools/shot.mjs --url=http://127.0.0.1:8000/menu --out=/tmp/menu.png \
  --width=390 --height=844 --reveal --scroll='#hookah-deluxe'
```
