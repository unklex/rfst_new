# Phase 1 — Complete

**Status**: ✅ Done — 2026-04-22
**Time**: ~15 min

## What landed

- Laravel `11.51.0` installed in `C:\Projects\crypton_site\` (via `_tmp` subdir + move pattern)
- Packages installed:
  - `filament/filament ^3.2`
  - `filament/spatie-laravel-settings-plugin ^3.2`
  - `filament/spatie-laravel-media-library-plugin ^3.2`
  - `spatie/laravel-medialibrary ^11`
  - `spatie/laravel-settings ^3`
  - `spatie/laravel-sluggable ^3`
  - `livewire/livewire ^3`
  - `laravel-lang/common ^6`
- Migrations published:
  - `database/migrations/2022_12_14_083707_create_settings_table.php` (Spatie Settings)
  - `database/migrations/2026_04_22_155826_create_media_table.php` (Spatie Media Library)
- `config/settings.php` published — **cache enabled** with file store, forever TTL, prefix `settings`
- Filament admin panel scaffolded at `/admin` — default `AdminPanelProvider` generated
- Russian translations installed via `lang:add ru`:
  - `lang/ru/actions.php`, `auth.php`, `http-statuses.php`, `pagination.php`, `passwords.php`, `validation.php`
  - `lang/ru.json`
- All Filament package translations published:
  - `lang/vendor/filament/ru/`
  - `lang/vendor/filament-actions/ru/`
  - `lang/vendor/filament-forms/ru/`
  - `lang/vendor/filament-infolists/ru/`
  - `lang/vendor/filament-notifications/ru/`
  - `lang/vendor/filament-panels/ru/`
  - `lang/vendor/filament-tables/ru/`
- Storage symlink created: `public/storage → storage/app/public`
- Tailwind removed per core decision (`tailwind.config.js`, `postcss.config.js` deleted; npm packages uninstalled)
- `.env` configured with Russian locale, Europe/Moscow TZ, SQLite, `MAIL_MAILER=log`, `QUEUE_CONNECTION=sync`, settings cache enabled, admin creds from env
- `.env.example` mirrors `.env` (without real `APP_KEY`)
- `resources/css/app.css` is a placeholder — real design CSS goes in Phase 3
- SQLite DB at `database/database.sqlite` with stock Laravel tables + `settings` + `media`

## Verification

| Check | Result |
|---|---|
| `php artisan --version` | `Laravel Framework 11.51.0` ✅ |
| `php artisan about` — locale | `ru` ✅ |
| `php artisan about` — timezone | `Europe/Moscow` ✅ |
| `php artisan about` — drivers: cache=file, queue=sync, mail=log, db=sqlite | ✅ |
| `php artisan route:list` — Filament routes | `/admin`, `/admin/login`, `/admin/logout` registered ✅ |
| `php artisan migrate` — all migrations | settings + media tables created ✅ |
| `lang/ru/` exists with framework translations | ✅ |
| `lang/vendor/filament*/ru/` exists for 7 Filament packages | ✅ |
| `curl -sI http://127.0.0.1:8000` | `HTTP/1.1 200 OK` ✅ |
| `curl -sI http://127.0.0.1:8000/admin/login` | `HTTP/1.1 200 OK` ✅ |
| `storage/framework/cache/` exists | ✅ |
| `config/settings.php` cache enabled | `'enabled' => env('SETTINGS_CACHE_ENABLED', true)` ✅ |
| Tailwind removed | no `tailwind.config.js`, `postcss.config.js` ✅ |

## Deviations from plan

1. **Media library tag**: plan said `--tag=media-library-migrations`, actual tag is `--tag=medialibrary-migrations` (no hyphen in "medialibrary"). Fixed in commands.
2. **Filament translations**: plan listed only `--tag=filament-translations`. Actually 7 sub-packages need separate publishes (filament-actions, filament-forms, filament-infolists, filament-notifications, filament-panels, filament-tables, filament-widgets). filament-widgets has no translations so it's skipped silently.
3. **Spatie Settings auto-discovery**: `config/settings.php` has `auto_discover_settings` pointing at `app_path('Settings')` by default. So we don't need to manually register the 20 classes — they'll be picked up automatically when we create them in Phase 2. Left the `settings` array empty.
4. **Tailwind explicitly removed**: plan didn't call this out but `core_decisions.md` dictates handwritten CSS. Removed here instead of leaving for Phase 3.

## Files changed/created in this phase

- Everything in project root except `.claude/` and `docs/` — Laravel default scaffold
- `.env`, `.env.example` — customized
- `config/settings.php` — cache enabled
- `database/database.sqlite` — empty DB
- `resources/css/app.css` — placeholder
- `public/storage` — symlink
- `lang/ru/`, `lang/ru.json`, `lang/vendor/filament*/ru/` — Russian translations

## Next — Phase 2

Write 20 Settings classes, 17 Eloquent models + migrations, 12 content seeders, the big settings-seed migration, and the `ContactRequestStatus` enum. End state: `php artisan migrate:fresh --seed` populates the full design copy.
