# Phase 2 — Complete

**Status**: ✅ Done — 2026-04-22
**Time**: ~40 min

## What landed

### 20 Settings classes (`app/Settings/`)

All extend `Spatie\LaravelSettings\Settings`. Auto-discovery finds them via `config/settings.php → auto_discover_settings`.

1. `GeneralSettings` — brand + SEO (includes `meta_description`)
2. `DesignSettings` — `signal` / `paper` / `head_weight` enum strings (the ex-tweaks panel)
3. `TopStripSettings` — dark top strip (status, location, license)
4. `NavSettings` — phone + primary CTA
5. `HeroSettings` — **27 properties** (ref code, headline, lede, 2 CTAs, signature, both hero cards)
6. `AboutSettings` — § 01 section
7. `MetricsSettings` — dark band header
8. `ServicesSectionSettings` — § 02 header
9. `BitrixSettings` — § 03 kicker/heading/paragraph + mock window chrome
10. `IndustriesSectionSettings` — § 04 header
11. `WasteSectionSettings` — § 05 header
12. `ProcessSectionSettings` — § 06 header
13. `PlansSectionSettings` — § 07 header
14. `CoverageSettings` — § 08 (kicker, heading, paragraph, map meta)
15. `QuoteSettings` — testimonial
16. `CtaBandSettings` — signal-colored band with two CTAs
17. `ContactSettings` — info rows + all form labels/placeholders + 152-ФЗ consent text
18. `FooterSettings` — about paragraph, copyright, legal links, massive wordmark
19. `LegalSettings` — INN, KPP, OGRN, license number/issuer/date
20. `IntegrationSettings` — Turnstile keys (`turnstile_secret_key` encrypted), notify_email, Metrika ID

### Settings seed migration

`database/settings/2026_04_22_120000_seed_all_settings.php` — ~170 `$blueprint->add()` calls copying exact Russian text from `index-v2.html` into the 20 groups. Runs on `php artisan migrate`; re-run with `migrate:fresh --seed`.

### Enum + trait

- `app/Enums/ContactRequestStatus.php` — two cases (`New`, `Handled`), implements Filament's `HasLabel` + `HasColor` (Russian labels, warning/success colors)
- `app/Models/Concerns/HasSortOrder.php` — `scopeOrdered()` (by `sort`, `id`), `scopeActive()` (where `is_active = true`)

### 18 Eloquent models + 18 migrations

| Model | Migration | Rows seeded | Key features |
|---|---|---|---|
| `NavItem` | `..110001` | 7 | label, anchor |
| `TickerItem` | `..110002` | 6 | single label |
| `AboutLedgerRow` | `..110003` | 4 | code + title_html + detail_html |
| `MetricTile` | `..110004` | 4 | value_html with `<sup>`/`<em>` |
| `Service` | `..110005` | 3 | json `spec_rows`, `is_featured` toggle |
| `BitrixFeature` | `..110006` | 4 | A.01–A.04 labeled features |
| `BitrixMockColumn` | `..110007` | 3 | title + badge |
| `BitrixMockCard` | `..110008` | 7 | FK to column, accent enum |
| `Industry` | `..110009` | 6 | full industry table row |
| `WasteType` | `..110010` | 8 (5 hazard) | **HasMedia + webp conversions** |
| `ProcessStep` | `..110011` | 5 | number, title, meta |
| `Plan` | `..110012` | 3 | json `features`, `is_highlighted` |
| `Region` | `..110013` | 10 | number, name |
| `MapPin` | `..110014` | 5 | coordinates + position_class c1..c5 |
| `FooterColumn` | `..110015` | 3 | heading |
| `FooterLink` | `..110016` | 15 | FK to column, external flag |
| `SiteAsset` | `..110017` | 5 keys | **HasMedia + per-key webp conversions** |
| `ContactRequest` | `..110018` | — | status enum cast, json `utm` |

All content models use `HasSortOrder` trait (has `ordered()` + `active()` scopes, `sort` + `is_active` columns).

### 13 seeders + orchestrator

- `AdminUserSeeder` — creates admin from env (`ADMIN_EMAIL` / `ADMIN_PASSWORD` / `ADMIN_NAME`), password hashed
- 12 content seeders listed above, each idempotent via `updateOrCreate(['sort' => N], ...)`
- `DatabaseSeeder` orchestrates all 13 + calls `Cache::flush()` at end to bust stale settings cache

## Verification

### Fresh seed

```bash
php artisan migrate:fresh --seed
```

All migrations applied, all seeders completed without error.

### Row counts (via tinker)

| Model | Expected | Actual |
|---|---|---|
| User | 1 | 1 ✅ |
| NavItem | 7 | 7 ✅ |
| TickerItem | 6 | 6 ✅ |
| AboutLedgerRow | 4 | 4 ✅ |
| MetricTile | 4 | 4 ✅ |
| Service (1 featured) | 3 | 3 ✅ |
| BitrixFeature | 4 | 4 ✅ |
| BitrixMockColumn | 3 | 3 ✅ |
| BitrixMockCard | 7 | 7 ✅ |
| Industry | 6 | 6 ✅ |
| WasteType (5 hazard) | 8 | 8 ✅ |
| ProcessStep | 5 | 5 ✅ |
| Plan (1 highlighted) | 3 | 3 ✅ |
| Region | 10 | 10 ✅ |
| MapPin | 5 | 5 ✅ |
| FooterColumn | 3 | 3 ✅ |
| FooterLink | 15 | 15 ✅ |
| SiteAsset | 5 | 5 ✅ |

### Settings reads

```
app(HeroSettings::class)->headline_html     → "Экология<br>как <em>инженерная</em><br>дисциплина."
app(ServicesSectionSettings::class)->section_heading_html → "Три направления, <em>один</em> оператор."
app(DesignSettings::class)->signal           → "hazard"
app(LegalSettings::class)->license_number   → "№ 077 № 00428"
```

### JSON casts

First `Plan::ordered()->first()->features` returns 5 Russian strings as PHP array — JSON casting works.

### HTTP liveness

- `GET /` → `200 OK`
- `GET /admin/login` → `200 OK`

## Deviations from plan

1. **BitrixMockCard count**: plan said "8 cards", actual is 7 (3+2+2). Re-counted against `index-v2.html` lines 794-810 — the design has 7. Updated plan's seeder docs to 7.
2. **WasteType hazard count**: plan said "4 hazard", actual is 5. Design has 5 cards marked `cls haz` (Производственные остатки, Электронный лом, Фармацевтические, Жидкие, Аккумуляторы). Design was under-counted in plan.
3. **SiteAsset seeder**: plan didn't list this as a separate seeder; rolled into Phase 4's resource in original plan. Moved here so Phase 3 can reference all 5 keys immediately. 14 total seeders now (13 called by DatabaseSeeder + AdminUserSeeder is #1).
4. **`IntegrationSettings::encrypted()`**: Spatie Settings supports per-class `encrypted()` method. Added for `turnstile_secret_key`.
5. **`ContactRequest::status`** cast as enum directly on the model (Laravel 11 native enum cast support) — no accessor/mutator needed.
6. **SiteAsset model** added `title` + `alt` columns beyond just `key` — useful for admin UX in Phase 4 (descriptive label and image alt text).
7. **Migration numbering**: 18 new migrations (plan said 16–17). Extras: `site_assets` got its own migration (was bundled in plan's text but needs its own file).

## Files created in Phase 2

```
app/Settings/                           (20 files)
  GeneralSettings.php         TopStripSettings.php     NavSettings.php
  HeroSettings.php            AboutSettings.php        MetricsSettings.php
  ServicesSectionSettings.php BitrixSettings.php       IndustriesSectionSettings.php
  WasteSectionSettings.php    ProcessSectionSettings.php PlansSectionSettings.php
  CoverageSettings.php        QuoteSettings.php        CtaBandSettings.php
  ContactSettings.php         FooterSettings.php       LegalSettings.php
  IntegrationSettings.php     DesignSettings.php

app/Models/                             (18 files)
  Concerns/HasSortOrder.php
  NavItem.php             TickerItem.php         AboutLedgerRow.php
  MetricTile.php          Service.php            BitrixFeature.php
  BitrixMockColumn.php    BitrixMockCard.php     Industry.php
  WasteType.php           ProcessStep.php        Plan.php
  Region.php              MapPin.php             FooterColumn.php
  FooterLink.php          SiteAsset.php          ContactRequest.php

app/Enums/
  ContactRequestStatus.php

database/migrations/                    (18 files)
  2026_04_22_110001..110018_*.php

database/settings/
  2026_04_22_120000_seed_all_settings.php

database/seeders/                       (14 files, overwrote DatabaseSeeder)
  DatabaseSeeder.php         AdminUserSeeder.php     NavItemSeeder.php
  TickerItemSeeder.php       AboutLedgerRowSeeder.php MetricTileSeeder.php
  ServiceSeeder.php          BitrixSeeder.php         IndustrySeeder.php
  WasteTypeSeeder.php        ProcessStepSeeder.php    PlanSeeder.php
  CoverageSeeder.php         FooterSeeder.php         SiteAssetSeeder.php
```

## Next — Phase 3

Port the design's CSS (verbatim) into `resources/css/app.css`. Write 18 Blade partials that consume these Settings/models and render the full homepage matching `index-v2.html`. No admin UI yet.
