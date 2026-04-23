# Phase 7 — Verification & Production Build

**Goal**: the project is shippable. Assets are production-compiled, all 14 verification checks pass, caches are warm, and the deploy docs (README, `.env.example`) reflect the final state.

## Prerequisites

- Phases 1–6 complete
- Homepage renders identically to `index-v2.html`
- Admin panel allows full content editing
- Lead submission pipeline works end-to-end

## Tasks

### 1. Production build

```bash
npm run build
```

Expect `public/build/` to contain hashed `app-*.css` and `app-*.js` bundles, plus `manifest.json`.

### 2. Framework caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 3. Filament optimization

```bash
php artisan filament:cache-components
php artisan icons:cache
```

### 4. Settings cache pre-warm

Hit the homepage once (or run a simple cache-warmer) so the first real visitor doesn't pay the cold-cache cost:

```bash
curl -s http://127.0.0.1:8000/ > /dev/null
```

### 5. Queue worker (if async mail in prod)

For production, switch `.env`:

```
QUEUE_CONNECTION=database
```

and run `php artisan queue:work` via Supervisor or equivalent. For dev you can leave `sync`.

### 6. Final `.env.example` audit

Every key that must be set on a fresh deploy is documented in `.env.example`. No real secrets committed. `grep -E "=.+" .env.example` should show only example/placeholder values.

### 7. README polish

Update the project root `README.md` (overwrite the stock Laravel one) with:

- 1-paragraph project description
- Stack
- Local setup: `composer install && npm install && cp .env.example .env && php artisan key:generate && php artisan migrate --seed && npm run dev && php artisan serve`
- Admin login: `/admin` with seeded credentials from env
- Reference to `docs/` phase files
- Reference to the design source

### 8. Add a `.gitignore` line for conversions cache

Already in Laravel's default, but double-check `storage/app/public/` and `public/build/` are git-ignored (the former contains user uploads, the latter contains compiled assets).

## Verification — the 14 checks

Run each and tick.

### Frontend

- [ ] **1. Visual parity** — `http://127.0.0.1:8000` matches `index-v2.html` pixel-for-pixel. Every class preserved. Animations (blinking dot, marquee, compass swing) active. Hover states on services/industries/bitrix-feature-rows work.

### Admin UX

- [ ] **2. Admin login** — `/admin` with env credentials → dashboard loads. UI **fully in Russian**: sidebar labels, form labels, pagination, validation errors.
- [ ] **3. Nav order** — sidebar groups in order **Контент → Справочники → Заявки → Настройки**. Inside Настройки, pages mirror landing flow (Общие → Палитра → Верхняя полоса → Меню → Hero → ... → Интеграции). No alphabetical fallback anywhere.

### Data + cache

- [ ] **4. Settings cache** — with DB query log enabled, first request performs N settings reads (cold), subsequent requests perform **zero** until any settings page saves.
- [ ] **5. Setting flip + cache bust** — edit `HeroSettings::headline_html` → save → reload `/` → H1 updates **immediately** (no manual cache clear). Same test for 2-3 other settings classes.

### Content collections

- [ ] **6. Model flip** — drag-reorder services → homepage order changes. Toggle `is_featured` → orange highlight moves. Toggle `is_active=false` on a service → it disappears from homepage.

### Media

- [ ] **7. Image upload + webp** — upload PNG to `SiteAsset::about_archive` → `storage/app/public/*/conversions/*-webp.webp` exists → `/` about section renders the webp (DevTools Network confirms `Content-Type: image/webp`).
- [ ] **8. Favicon + OG** — set `SiteAsset::favicon` + `SiteAsset::og_image` → view source shows `<link rel="icon">` and `<meta property="og:image">` → `<meta name="description">` reflects `GeneralSettings::meta_description`.

### Design switching

- [ ] **9. Design palette flip** — `DesignSettings::paper = noir` → save → reload → entire site dark via `[data-paper="noir"]` CSS. Same for `signal` (orange → iron/indigo/blood) and `head_weight` (serif → sans).

### Lead pipeline

- [ ] **10. Lead submit** — fill form with valid data → submit → row in `contact_requests` (status=`new`), mail body in `storage/logs/laravel.log`, Filament **Заявки** badge increments.
- [ ] **11. Honeypot** — POST with `website=bot` → silent drop, no row, no mail.
- [ ] **12. Rate limit** — 6 rapid submits from same IP → 6th returns Russian "слишком много попыток".

### Turnstile

- [ ] **13. Turnstile from settings** — set both keys via admin → widget renders with the key in DOM. `grep -r "env('TURNSTILE" app/ resources/` returns **zero** matches.
- [ ] **14. Turnstile bypass + test keys** — empty secret passes (dev mode). Cloudflare test pair (`1x000...AA` / `1x0000...AA`) passes. Bad secret rejects with Russian error.

### Bonus integrity checks

- [ ] `php artisan route:list | grep -v "filament\|livewire"` — public routes are just `/`, `/up`, and `/storage/*` (stock). No debug/test routes leaking.
- [ ] `php artisan about` — confirms cache/config/events/views all cached.
- [ ] `composer audit` — no high-severity CVEs.
- [ ] `npm audit` — no high-severity CVEs in production dependencies.

## Deploy readiness

Before shipping to production:

- [ ] Switch `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Generate a fresh `APP_KEY` on the prod server (`php artisan key:generate`)
- [ ] Set real `MAIL_*` SMTP config (not `log`)
- [ ] Set real `ADMIN_EMAIL`/`ADMIN_PASSWORD` (change from defaults!)
- [ ] Set `QUEUE_CONNECTION=database` + run `php artisan queue:table && php artisan migrate`
- [ ] Configure a queue worker via Supervisor
- [ ] Set up Cloudflare Turnstile at `dash.cloudflare.com/?to=/:account/turnstile`, add keys via admin UI (not env)
- [ ] Configure Cloudflare CDN / HTTPS
- [ ] Add a database backup cron (`spatie/laravel-backup` is a good fit if you want it)
- [ ] Add error monitoring (Sentry, Bugsnag, or Flare) if desired

## Common pitfalls (production)

- **`php artisan config:cache` freezes env values**: after any `.env` change on prod, re-run `config:cache`. Never reference `env()` outside `config/*` files.
- **Missing symlink**: `storage:link` must be run on prod (or use a deploy script).
- **`APP_URL` mismatch**: Filament generates absolute URLs for login redirects; wrong `APP_URL` causes broken redirects through HTTPS proxies.
- **Turnstile widget fails silently on production**: domain not added to Cloudflare Turnstile widget configuration.
- **`/admin` 404**: route cache from before Filament install. Run `php artisan route:clear && php artisan route:cache`.
- **Queue worker stale**: Supervisor must restart the worker on deploys (`php artisan queue:restart` in post-deploy).

## Post-launch

- Monitor `storage/logs/laravel.log` for the first week — lead-submission errors, cache misses, queue failures surface here.
- Set up an uptime monitor pointing at `/up` (Laravel's health endpoint).
- Decide whether to add Yandex Metrika — `IntegrationSettings::yandex_metrika_id` already exists; the blade injection was scaffolded in Phase 6 but verify it's wired.

---

Project is shippable when all 14 boxes above are ticked. Congrats.
