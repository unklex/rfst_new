# Crypton Site — Implementation Docs

Greenfield Laravel 11 + Filament 3 build of the **"Industrial Ledger"** design for ООО «Криптон» (промышленная экология + Битрикс24).

## What this folder is

Seven self-contained phase guides. Work through them in order — each phase ends at a verifiable stopping point so you (or the next agent) can inspect, correct, and only then proceed.

## Phase index

| # | File | Goal | Ends when |
|---|---|---|---|
| 1 | [phase-01-scaffold.md](phase-01-scaffold.md) | Bootstrap Laravel + packages + translations | `php artisan serve` shows default Laravel page in Russian |
| 2 | [phase-02-data-model.md](phase-02-data-model.md) | 20 Settings classes + 17 models + 12 seeders + webp conversions | `migrate:fresh --seed` populates DB with the full design copy |
| 3 | [phase-03-frontend.md](phase-03-frontend.md) | CSS port + 18 Blade partials reading from settings/models | `/` is pixel-identical to `index-v2.html` |
| 4 | [phase-04-filament-admin.md](phase-04-filament-admin.md) | Admin panel: 20 Settings pages + 16 Resources with nav sort + cache bust | `/admin` lets an editor flip any copy or image and see it on `/` immediately |
| 5 | [phase-05-contact-form.md](phase-05-contact-form.md) | Livewire form + action + mailable + Filament ContactRequestResource | Submitting the form writes a row + logs a mail; honeypot/rate-limit/Turnstile work |
| 6 | [phase-06-media-seo.md](phase-06-media-seo.md) | Favicon, OG, Twitter, meta description — all from settings | Fresh upload produces webp variants; view-source shows full SEO head |
| 7 | [phase-07-verification.md](phase-07-verification.md) | Production build + 14-point verification checklist | All 14 checks pass; site is ready to ship |

## Stack (confirmed)

- PHP 8.2 · Laravel `^11.31` · Filament 3.2 · Livewire 3
- Spatie: laravel-settings 3, laravel-medialibrary 11, laravel-sluggable 3
- laravel-lang/common 6 (Russian translations)
- Vite, no Tailwind — handwritten CSS ported verbatim from the prototype
- SQLite (dev) / MySQL (prod), file cache for settings

## Ground rules (don't break these)

1. **CSS stays verbatim** — paste the `<style>` block from `index-v2.html` into `resources/css/app.css`. Do not "modernize" or convert to Tailwind.
2. **Every class name preserved** — the port is a copy of the HTML into Blade echo tags, nothing restructured.
3. **Turnstile keys live in `IntegrationSettings`**, never `env()` (config:cache would freeze stale values).
4. **Settings cache must be enabled** in `config/settings.php` — 20 settings injections per request otherwise.
5. **`$navigationSort` is mandatory** on every Filament page/resource — admin menu mirrors top-to-bottom landing flow.
6. **Russian only.** `APP_LOCALE=ru`, `APP_FALLBACK_LOCALE=ru`. The `рус/en` strip toggle is a static link.
7. **Seed with design copy** so the site renders identically after `migrate --seed`.
8. **`registerMediaConversions` required** on `SiteAsset` + `WasteType` — webp variants, sized per role.

## Reference material

- **Design source of truth**: `C:\Users\user\.claude\projects\C--Projects-crypton-site\design-extract\rfst\project\index-v2.html` (1269 lines)
- **Master plan**: `C:\Users\user\.claude\plans\fetch-this-design-file-sunny-sunset.md`
- **Port-from project** (orders logic, Filament patterns): `C:\Projects\rosecology_new\app\`
  - `app/Actions/Contact/SubmitContactRequestAction.php`
  - `app/Actions/Contact/VerifyTurnstileAction.php`
  - `app/Livewire/ContactForm.php`
  - `app/Filament/Resources/ContactRequestResource.php`
  - `app/Providers/Filament/AdminPanelProvider.php`
  - `app/Settings/IntegrationSettings.php`, `SiteSettings.php`
  - `database/migrations/2026_04_20_130002_create_contact_requests_table.php`

## How to execute a phase with Claude

1. Open the phase file. Read the **Goal** and **Prerequisites** to confirm you're at the right starting point.
2. Tell Claude: *"Execute phase N from docs/phase-0N-<name>.md."*
3. Claude runs through the **Tasks** list and reports at the **Verification** checkpoint.
4. Inspect the result. If broken, ask Claude to fix. If good, move to the next phase.
5. Do not skip phases — Phase 3 assumes Phase 2's models exist; Phase 5 assumes Phase 4's admin panel.

## Progress tracking

- [x] Phase 1 — Scaffold
- [x] Phase 2 — Data model
- [x] Phase 3 — Frontend port
- [x] Phase 4 — Filament admin
- [x] Phase 5 — Contact form
- [x] Phase 6 — Media + SEO
- [ ] Phase 7 — Verification + build
