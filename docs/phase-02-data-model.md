# Phase 2 — Data Model

**Goal**: every byte of the design's text and every repeated item has a persistent home. Write all 20 Settings classes, 17 Eloquent models, 12 seeders, and the one big migration that seeds default settings values from the design copy. End state: `php artisan migrate:fresh --seed` produces a database that, once rendered, reproduces `index-v2.html` exactly.

## Prerequisites

- Phase 1 complete: Laravel running, packages installed, `config/settings.php` has cache + registered class list
- `database/database.sqlite` exists

## Reference

- Design copy source: `C:\Users\user\.claude\projects\C--Projects-crypton-site\design-extract\rfst\project\index-v2.html`
- Rosecology patterns: `C:\Projects\rosecology_new\app\app\Settings\*.php` (study `SiteSettings.php` and `IntegrationSettings.php` for property-declaration style)

## Tasks

### A. Settings classes (20 files in `app/Settings/`)

Each file extends `Spatie\LaravelSettings\Settings` with public typed properties and a static `group()` method. See the **Data model → A** table in `C:\Users\user\.claude\plans\fetch-this-design-file-sunny-sunset.md` for the full property list per class.

Order of creation (matches landing-page flow):

1. `GeneralSettings` — group `general` — includes `meta_description`
2. `DesignSettings` — group `design` — three enum strings
3. `TopStripSettings` — group `strip`
4. `NavSettings` — group `nav`
5. `HeroSettings` — group `hero` — biggest class, ~20 props
6. `AboutSettings` — group `about`
7. `MetricsSettings` — group `metrics`
8. `ServicesSectionSettings` — group `services_section`
9. `BitrixSettings` — group `bitrix`
10. `IndustriesSectionSettings` — group `industries_section`
11. `WasteSectionSettings` — group `waste_section`
12. `ProcessSectionSettings` — group `process_section`
13. `PlansSectionSettings` — group `plans_section`
14. `CoverageSettings` — group `coverage`
15. `QuoteSettings` — group `quote`
16. `CtaBandSettings` — group `cta_band`
17. `ContactSettings` — group `contact`
18. `FooterSettings` — group `footer`
19. `LegalSettings` — group `legal`
20. `IntegrationSettings` — group `integrations` — `turnstile_secret_key` cast as `encrypted`

### B. Settings defaults migration

File: `database/migrations/2026_04_22_120000_seed_all_settings.php`

One Spatie-settings migration, using `->inGroup(...)` blocks per group. Every `$b->add('property_name', 'exact value from index-v2.html')` literal comes directly from the design. Keep the raw Russian text with `<em>`/`<b>` tags for HTML properties.

Example for the hero group (abbreviated):

```php
$this->migrator->inGroup('hero', function (SettingsBlueprint $b) {
    $b->add('ref_code_html', "<b>REF / 2026-Q2 · ED. 014</b><br>документ № RF-ST · гл. / ru<br>→ обновлено 14 апр. 2026");
    $b->add('hazard_label', 'III–IV');
    $b->add('headline_html', 'Экология<br>как <em>инженерная</em><br>дисциплина.');
    $b->add('lede_html', '<b>ООО «Криптон»</b> — полный цикл управления отходами III–IV класса...');
    // ... all other hero props
});
```

Do one group at a time; diff against `index-v2.html` as you go.

### C. Eloquent models + migrations (17)

Create in this order (migration timestamps ascending):

1. `NavItem` — `nav_items`
2. `TickerItem` — `ticker_items`
3. `AboutLedgerRow` — `about_ledger_rows`
4. `MetricTile` — `metric_tiles`
5. `Service` — `services`
6. `BitrixFeature` — `bitrix_features`
7. `BitrixMockColumn` — `bitrix_mock_columns`
8. `BitrixMockCard` — `bitrix_mock_cards` (FK to columns)
9. `Industry` — `industries`
10. `WasteType` — `waste_types` — **implements `HasMedia`**
11. `ProcessStep` — `process_steps`
12. `Plan` — `plans`
13. `Region` — `regions`
14. `MapPin` — `map_pins`
15. `FooterColumn` — `footer_columns`
16. `FooterLink` — `footer_links` (FK to columns)
17. `SiteAsset` — `site_assets` — **implements `HasMedia`** — unique `key` column
18. `ContactRequest` — `contact_requests` — slim (drop all `fastapi_*` / `forwarded_at` / `external_id` / `service_id` columns from rosecology's migration)

Column lists: see the **Data model → B** table in the master plan.

### D. Traits and enum

- `app/Models/Concerns/HasSortOrder.php` — adds `scopeOrdered(fn($q) => $q->orderBy('sort'))` and `scopeActive(fn($q) => $q->where('is_active', true))`
- `app/Enums/ContactRequestStatus.php` — two cases: `New = 'new'` (label "Новая", color `warning`), `Handled = 'handled'` (label "Обработана", color `success`). Implements `HasLabel` + `HasColor` (Filament contracts).

### E. Media conversions

On `SiteAsset`:

```php
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('webp')->format('webp')->quality(82)->width(match ($this->key) {
        'hero_bg', 'og_image' => 1920,
        'about_archive'        => 1280,
        'quote_reviewer'       => 480,
        'favicon'              => 180,
        default                => 1280,
    })->nonQueued();

    if ($this->key !== 'favicon') {
        $this->addMediaConversion('webp_mobile')->format('webp')->quality(78)->width(720)->nonQueued();
    }
}
```

On `WasteType`:

```php
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('webp')->format('webp')->quality(82)->width(640)->nonQueued();
    $this->addMediaConversion('webp_thumb')->format('webp')->quality(78)->width(320)->nonQueued();
}
```

### F. Seeders (12 content + 1 admin)

All in `database/seeders/`. Use `Model::updateOrCreate(['sort' => $i], [...])` for idempotency.

1. `AdminUserSeeder` — reads `ADMIN_EMAIL`/`ADMIN_PASSWORD`/`ADMIN_NAME` from env, creates `User` with hashed password
2. `NavItemSeeder` — 7 items from index-v2.html lines 553-561
3. `TickerItemSeeder` — 6 unique labels from 626-650
4. `AboutLedgerRowSeeder` — 4 rows from 672-675
5. `MetricTileSeeder` — 4 tiles from 691-710
6. `ServiceSeeder` — 3 services from 727-764 (third has `is_featured=true`)
7. `BitrixSeeder` — 4 features (778-781) + 3 mock columns + 8 mock cards (794-810)
8. `IndustrySeeder` — 6 rows from 835-876
9. `WasteTypeSeeder` — 8 cards from 894-949 (four have `is_hazard=true`)
10. `ProcessStepSeeder` — 5 steps from 966-995
11. `PlanSeeder` — 3 plans from 1012-1049 (middle has `is_highlighted=true`)
12. `CoverageSeeder` — 10 regions (1063-1072) + 5 map pins (1077-1081)
13. `FooterSeeder` — 3 columns × 5 links from 1172-1200

`DatabaseSeeder::run()` calls all of the above in order, then `Cache::forget('settings.*')` at the end to bust stale settings cache from prior runs.

### G. `settings:clear-cache` Artisan command

`app/Console/Commands/ClearSettingsCache.php` — 20-line wrapper around `Cache::forget()` for each registered group, useful in deploy scripts.

## Verification

Run:

```bash
php artisan migrate:fresh --seed
php artisan tinker
```

In tinker:

```php
>>> app(\App\Settings\HeroSettings::class)->headline_html
# => "Экология<br>как <em>инженерная</em><br>дисциплина."

>>> \App\Models\Service::ordered()->count()
# => 3

>>> \App\Models\WasteType::where('is_hazard', true)->count()
# => 4

>>> \App\Models\User::first()->email
# => "admin@crypton.local"
```

Checklist:

- [ ] 20 Settings classes in `app/Settings/` — each extends `Spatie\LaravelSettings\Settings`
- [ ] Settings-seed migration runs without errors
- [ ] `php artisan tinker` can read a setting from every class
- [ ] 17 model migrations applied (SQLite inspector or `php artisan schema:dump --prune` + diff)
- [ ] All 12 content seeders produce the expected row counts
- [ ] `AdminUserSeeder` creates a single `users` row
- [ ] `ContactRequestStatus` enum cases + colors resolve
- [ ] `SiteAsset::registerMediaConversions()` and `WasteType::registerMediaConversions()` exist
- [ ] Settings cache file exists in `storage/framework/cache/` after first read

## Common pitfalls

- **Migration hangs on Spatie settings**: old cached config. Run `php artisan config:clear` then migrate again.
- **"Undefined property" when reading a setting**: class isn't listed in `config/settings.php → settings` array, or the property wasn't added in the seed migration.
- **SQLite FK failures**: enable `PRAGMA foreign_keys = ON` (Laravel does this by default, but check for typos in FK migrations).
- **Seeder not idempotent**: `updateOrCreate` must key on `sort` (unique per model here). Don't use `create()` or re-running seed will duplicate rows.
- **Forgetting `Cache::forget('settings.*')` in `DatabaseSeeder`**: first request after seed will read stale cached nulls.

## Next

Phase 3 ports the CSS and writes 18 Blade partials that consume these settings/models. Don't wire Filament yet — admin wiring is Phase 4 and needs the homepage working first for the "edit → flip" verification loop.
