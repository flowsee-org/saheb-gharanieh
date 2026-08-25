# کافه صاحبقرانیه — Saheb Gharaniyeh Cafe

A mobile-first digital menu for a traditional Persian café. Deep black and antique gold,
ornate line-art frames, full RTL, Persian typography — modelled on the café's printed
four-panel menu (`images/photo_5767233449319141139_y.jpg`).

No cart, no checkout, no payments: it is a menu you read on a phone at the table.

---

## Getting started

```bash
composer install
npm install

cp .env.example .env         # already done in this checkout
php artisan key:generate

php artisan migrate:fresh --seed   # schema + the real menu from the printed reference
npm run build                      # or: npm run dev

php artisan serve
```

Run the test suite with `php artisan test`.

The database is SQLite (`database/database.sqlite`) out of the box; nothing else is required.

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
opt-in: the same printed menu on cream card stock.

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
- Ramp semantics are preserved: a low gold index still means "for headings", it is just
  deep bronze on paper instead of pale gold on black.

### Sticky section bar

`resources/js/app.js` runs a rAF-throttled scroll spy: the current section is the last one
whose top edge has passed under the bar. It swaps the label in `#section-flag-text`, marks
the matching chip with `aria-current="true"`, scrolls that chip into view, updates the URL
hash with `history.replaceState`, and drives the thin gold progress rule. Anchor clicks are
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
| `price`, `price_note` | One service price for the whole section (the hookah panels) |
| `card_order`, `card_title`, `card_subtitle`, `card_latin` | Landing-page card. `card_order = NULL` means "not on the landing page" |
| `sort_order`, `is_active` | Ordering and visibility |

**`products`** — one row per item. `price` is nullable on purpose: the printed menu leaves
`قیمت :` blank, so an empty price renders a dotted gold slot instead of a number.
`image_path` is nullable too — when it is empty the card shows the ornate placeholder.
`is_active` hides an item, `is_available` renders it as "موقتاً تمام شد".

**`category_features`** — the extras strip under the Super Deluxe hookah panel
(چای زغالی، میوه فصل، باقلوا …).

**`settings`** — editable site copy (café name, tagline, intro paragraph, hours, address,
phone, Instagram) as `key`/`value` rows. Read through `Setting::map()`, which is cached and
busted automatically on save/delete.

Seeders hold the real menu transcribed from the reference photo: 15 hot drinks, 18 cold
drinks and 16 hookah flavours (seeded into both hookah services), plus 8 deluxe extras.
They use `updateOrCreate`, so re-running them is safe. `tests/Feature/MenuSeederTest.php`
locks those counts in.

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
| `/wp-admin/categories/create`, `…/{id}/edit` | Section form: copy, kind, layout, service price, landing-page card |
| `/wp-admin/settings` | The `settings` rows — café intro, hours, address, Instagram |
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
- Self-hosted variable fonts in `public/fonts`: Vazirmatn for Persian, Cinzel for the latin
  small-caps lines.
- Blade anonymous components in `resources/views/components`: `frame`, `ornament.*`,
  `icon.*`, `product-card`, `flavor-row`, `price-tag`, `emblem`, `theme-toggle`,
  `site-footer`.
- `@fa(...)` prints Persian digits and `@price(...)` prints a Persian price with " تومان"
  (see `App\Support\Persian` and `AppServiceProvider`).
- Vanilla JS only: theme switch, preloader, IntersectionObserver reveals, image fade-in,
  scroll spy, back-to-top. `prefers-reduced-motion` is respected.
- Four Vite entries: `app.css` / `app.js` for the menu, `admin.css` / `admin.js` for the
  panel. `php artisan test` leaves Vite switched on, so an entry missing from
  `public/build/manifest.json` fails the suite rather than 500-ing in production.

### Screenshots during development

`tools/shot.mjs` drives headless Chrome over CDP for mobile-viewport captures and in-page
diagnostics (overflow check, current section flag):

```bash
node tools/shot.mjs --url=http://127.0.0.1:8000/menu --out=/tmp/menu.png \
  --width=390 --height=844 --reveal --scroll='#hookah-deluxe'
```
