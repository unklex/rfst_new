# Phase 1 — Scaffold

**Goal**: Bootstrap an empty Laravel 11 project with all packages installed, translations published, and the settings cache profile enabled. End state is a stock Laravel welcome page served in Russian with all dev dependencies ready.

## Prerequisites

- PHP 8.2+ in `PATH`
- Composer 2.x
- Node 18+ / npm
- `C:\Projects\crypton_site\` exists and contains only `.claude/` and `docs/`

## Tasks

### 1. Install Laravel into the project root

Because `composer create-project .` refuses non-empty directories, install into a temp subdir and move files up:

```bash
cd C:\Projects\crypton_site
composer create-project laravel/laravel _tmp "^11" --no-interaction
# Move everything from _tmp into the project root, including dotfiles
mv _tmp/.[!.]* _tmp/.??* . 2>/dev/null || true
mv _tmp/* .
rmdir _tmp
```

### 2. Install runtime packages

```bash
composer require \
  filament/filament:^3.2 \
  filament/spatie-laravel-settings-plugin:^3.2 \
  filament/spatie-laravel-media-library-plugin:^3.2 \
  spatie/laravel-medialibrary:^11 \
  spatie/laravel-settings:^3 \
  spatie/laravel-sluggable:^3 \
  livewire/livewire:^3 \
  laravel-lang/common:^6
```

### 3. Publish migrations and configs

```bash
php artisan vendor:publish --tag=media-library-migrations
php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag=migrations
php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag=config
```

### 4. Enable settings cache (mandatory)

Edit `config/settings.php`:

```php
'cache' => [
    'enabled' => true,
    'store'   => env('SETTINGS_CACHE_STORE', 'file'),
    'prefix'  => 'settings',
    'ttl'     => null,
],
```

### 5. Register setting classes

Add the 20 class references to the `settings` array in `config/settings.php` (the classes themselves are written in Phase 2 — this is just pre-registration so the autoloader finds them):

```php
'settings' => [
    \App\Settings\GeneralSettings::class,
    \App\Settings\DesignSettings::class,
    \App\Settings\TopStripSettings::class,
    \App\Settings\NavSettings::class,
    \App\Settings\HeroSettings::class,
    \App\Settings\AboutSettings::class,
    \App\Settings\MetricsSettings::class,
    \App\Settings\ServicesSectionSettings::class,
    \App\Settings\BitrixSettings::class,
    \App\Settings\IndustriesSectionSettings::class,
    \App\Settings\WasteSectionSettings::class,
    \App\Settings\ProcessSectionSettings::class,
    \App\Settings\PlansSectionSettings::class,
    \App\Settings\CoverageSettings::class,
    \App\Settings\QuoteSettings::class,
    \App\Settings\CtaBandSettings::class,
    \App\Settings\ContactSettings::class,
    \App\Settings\FooterSettings::class,
    \App\Settings\LegalSettings::class,
    \App\Settings\IntegrationSettings::class,
],
```

### 6. Install Filament + Russian translations

```bash
php artisan filament:install --panels
# Creates App\Providers\Filament\AdminPanelProvider (we'll customize in Phase 4)

php artisan lang:add ru
php artisan vendor:publish --tag=filament-translations
```

### 7. Storage symlink

```bash
php artisan storage:link
```

### 8. Write `.env`

Copy `.env.example` → `.env`, then set:

```
APP_NAME=Крипт он
APP_LOCALE=ru
APP_FALLBACK_LOCALE=ru
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
# (leave DB_HOST/DB_PORT/etc commented for SQLite)

MAIL_MAILER=log
MAIL_TO_ADMIN=admin@crypton.local

ADMIN_EMAIL=admin@crypton.local
ADMIN_PASSWORD=change-me
ADMIN_NAME=Администратор

SETTINGS_CACHE_STORE=file

# NOTE: TURNSTILE keys live in IntegrationSettings (admin UI), NOT here.
```

Create the SQLite file:

```bash
touch database/database.sqlite
```

Generate app key:

```bash
php artisan key:generate
```

### 9. Also update `.env.example`

Mirror the keys above (minus real secrets) into `.env.example` so the repo documents the environment.

### 10. First migration sanity check

```bash
php artisan migrate
```

Should run cleanly: stock Laravel tables + `settings` + `media` + Filament's tables.

## Verification

- [ ] `composer install` completes with no errors
- [ ] `php artisan --version` prints `Laravel Framework 11.x`
- [ ] `php artisan route:list | grep filament` shows panel routes
- [ ] `lang/ru/` folder exists with auth/validation/passwords/pagination files
- [ ] `vendor/filament/filament/resources/lang/ru/` exists (Russian Filament strings)
- [ ] `database/database.sqlite` exists
- [ ] `php artisan serve` → `http://127.0.0.1:8000` shows the stock Laravel welcome page
- [ ] `config/settings.php` has `'cache' => ['enabled' => true, ...]`

## Common pitfalls

- **`composer create-project` fails "directory not empty"**: ensure `_tmp` subdir pattern is used (step 1). Move dotfiles too — `_tmp/.gitignore`, `_tmp/.env.example` are easy to miss on Windows.
- **Filament asks for a panel ID**: accept the default `admin`.
- **`php artisan lang:add ru` not found**: ensure `laravel-lang/common` is installed first, and run `composer dump-autoload`.
- **SQLite driver missing**: enable `pdo_sqlite` extension in `php.ini`.

## Next

Phase 2 writes the 20 Settings classes and their seed migration. Don't skip ahead — Filament's settings plugin will complain about unregistered settings classes if you try to install Phase 4 before Phase 2's classes exist.
