# Tech Stack

Single source of truth for versions, drivers, and runtime configuration. Keep this in sync with `composer.json` and `.env.example`.

## Runtime

| Layer | Version | Notes |
|---|---|---|
| PHP | 8.2+ | Windows dev, Linux prod. Extensions: `pdo_sqlite`, `gd` (webp), `mbstring`, `openssl`, `fileinfo`, `curl`, `zip` |
| Laravel | `^11.31` | Framework |
| Node | 18+ | For Vite asset build |
| npm | 10+ | Package manager |
| Composer | 2.x | Package manager |

## Framework packages

| Package | Version | Purpose |
|---|---|---|
| `filament/filament` | `^3.2` | Admin panel |
| `filament/spatie-laravel-settings-plugin` | `^3.2` | Settings pages in Filament |
| `filament/spatie-laravel-media-library-plugin` | `^3.2` | File upload fields |
| `spatie/laravel-medialibrary` | `^11` | Image uploads + conversions |
| `spatie/laravel-settings` | `^3` | Typed settings classes + caching |
| `spatie/laravel-sluggable` | `^3` | Slugs (reserved for future) |
| `livewire/livewire` | `^3` | Reactive contact form |
| `laravel-lang/common` | `^6` | Russian translations for framework, Filament, validation |

Dev dependencies: stock Laravel (`fakerphp/faker`, `laravel/pint`, `mockery/mockery`, `nunomaduro/collision`, `phpunit/phpunit ^11`).

## Data store

- **Dev**: SQLite at `database/database.sqlite`
- **Prod**: MySQL 8 / MariaDB 10.6+ (to be configured per deploy)
- **Cache store**: file (`storage/framework/cache/`) — settings cache TTL: forever, invalidated on save
- **Queue**: `sync` in dev, `database` in prod
- **Mail**: `log` in dev, real SMTP in prod

## Frontend

- **Build tool**: Vite (stock Laravel scaffold)
- **CSS strategy**: handwritten, ported verbatim from design prototype. NO Tailwind, NO CSS framework.
- **JS**: Alpine.js (via Livewire), plus a tiny smooth-scroll script
- **Fonts**: IBM Plex Serif/Sans/Mono from Google Fonts (Cyrillic subset)

## Media pipeline

- **Disk**: `public` (symlinked to `public/storage/`)
- **Conversions**: non-queued, eager — `webp` at role-specific widths
- **Optimizers**: Spatie Image Optimizer with fallback chain (jpegoptim, pngquant, cwebp)
- **Driver**: GD (fallback) or Imagick (preferred if available)

## Security

- **CSRF**: stock Laravel
- **Anti-spam**: honeypot (`website` field), rate limit (5 req / 60s / IP), Cloudflare Turnstile
- **Consent**: SHA-256 hash of displayed consent text stored per lead (audit trail for 152-ФЗ compliance)
- **Encrypted fields**: `IntegrationSettings::turnstile_secret_key` cast as `encrypted` (Laravel app-key encryption at rest)

## Localization

- `APP_LOCALE=ru` / `APP_FALLBACK_LOCALE=ru`
- `lang/ru/*.json` + framework translations via `laravel-lang/common`
- Filament translations via `php artisan vendor:publish --tag=filament-translations`

## Admin

- Route: `/admin` (Filament default)
- Auth: stock Laravel, single-tenant (any authenticated user is admin)
- Seeded admin: from env `ADMIN_EMAIL` / `ADMIN_PASSWORD` / `ADMIN_NAME`
- Groups (in sidebar order): `Контент` → `Справочники` → `Заявки` → `Настройки`
- Every page/resource has `$navigationSort` mirroring landing-page flow

## Observability

- `storage/logs/laravel.log` — stock Laravel log
- `/up` — Laravel health endpoint
- No Sentry/Flare in baseline (add post-launch if needed)

## External services

- **Cloudflare Turnstile** — bot check on contact form. Keys live in `IntegrationSettings`, not `.env`. Empty secret = bypass (dev mode).
- **Yandex Metrika** — optional, ID in `IntegrationSettings::yandex_metrika_id`

## Port policy

Code ported from `C:\Projects\rosecology_new\app`:
- `app/Actions/Contact/SubmitContactRequestAction.php` — surgical edits only (see Phase 5 doc)
- `app/Actions/Contact/VerifyTurnstileAction.php` — verbatim
- `app/Livewire/ContactForm.php` — drop `service_id`, tighten email/message rules
- `app/Filament/Resources/ContactRequestResource.php` — drop FastAPI fields/actions
- `database/migrations/*create_contact_requests_table.php` — drop `fastapi_*`, `forwarded_at`, `external_id`, `service_id` columns

Everything else is new to this project.
