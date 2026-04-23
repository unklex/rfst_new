# Phase 4 — Complete

**Status**: ✅ Done — 2026-04-23
**Time**: ~45 min

## What landed

### AdminPanelProvider

`app/Providers/Filament/AdminPanelProvider.php`:

- `->brandName('Криптон · Админка')`
- `->colors(['primary' => Color::Orange])` — matches the `signal` palette
- `->font('IBM Plex Sans', provider: GoogleFontProvider::class)` — same face as the public site
- `->navigationGroups(['Контент', 'Справочники', 'Заявки', 'Настройки'])` — visible sidebar order
- `->discoverPages(...)` + `->discoverResources(...)` — auto-pick up every page/resource
- `->login()` — stock login page with Filament's Russian translations
- `FilamentInfoWidget` intentionally removed (we keep `AccountWidget`)

No explicit `->plugins([...])` call is needed — `filament/spatie-laravel-settings-plugin` and `filament/spatie-laravel-media-library-plugin` register themselves via service providers; `Filament\Pages\SettingsPage`, `SpatieMediaLibraryFileUpload`, and `SpatieMediaLibraryImageColumn` are available out of the box.

### User → FilamentUser

`app/Models/User.php` now `implements FilamentUser` with `canAccessPanel(Panel $panel): bool { return true; }` (single-tenant).

### Settings pages (20 total, `app/Filament/Pages/`)

Every Settings page declares `$navigationGroup = 'Настройки'`, `$navigationSort`, Russian `$navigationLabel` + `$title`, and `use BustsSettingsCache` (a trait described below).

| # | Sort | Class | Group/section |
|---|---|---|---|
| 1 | 10  | `ManageGeneralSettings` | Общие — site name, tagline, meta description, brand identity |
| 2 | 20  | `ManageDesignSettings` | Палитра — `signal` / `paper` / `head_weight` via `Select` |
| 3 | 30  | `ManageTopStripSettings` | Верхняя полоса — status, location, license, messenger URLs |
| 4 | 40  | `ManageNavSettings` | Меню — phone + primary CTA label |
| 5 | 50  | `ManageHeroSettings` | Hero — 27 fields grouped into 5 sections (ref code, headline, CTAs, signature, cards A+B) |
| 6 | 60  | `ManageAboutSettings` | О компании — section head + body + CTA |
| 7 | 70  | `ManageMetricsSettings` | Метрики — dark band header |
| 8 | 80  | `ManageServicesSectionSettings` | Услуги — section head |
| 9 | 90  | `ManageBitrixSettings` | Битрикс24 — intro + mock window chrome |
| 10 | 100 | `ManageIndustriesSectionSettings` | Отрасли — section head |
| 11 | 110 | `ManageWasteSectionSettings` | Отходы — section head |
| 12 | 120 | `ManageProcessSectionSettings` | Процесс — section head |
| 13 | 130 | `ManagePlansSectionSettings` | Тарифы — section head |
| 14 | 140 | `ManageCoverageSettings` | География — kicker, heading, paragraph, map meta |
| 15 | 150 | `ManageQuoteSettings` | Цитата — reviewer + quote + company |
| 16 | 160 | `ManageCtaBandSettings` | CTA-полоса — headline + 2 buttons |
| 17 | 170 | `ManageContactSettings` | Контактная форма — info rows + 11 field labels + 152-ФЗ consent |
| 18 | 180 | `ManageFooterSettings` | Футер — about paragraph, copyright, 3 legal links, massive wordmark |
| 19 | 190 | `ManageLegalSettings` | Юр. реквизиты — INN/KPP/OGRN/license |
| 20 | 200 | `ManageIntegrationSettings` | Интеграции — Turnstile (`password()->revealable()`), notify email, Metrika ID |

`RichEditor` with `->toolbarButtons(['bold', 'italic', 'link', 'undo', 'redo'])` is used for every `*_html` property (23 properties across the 20 pages). Plain strings use `TextInput` or `Textarea`.

### BustsSettingsCache trait

`app/Filament/Concerns/BustsSettingsCache.php` — shared `afterSave()` used by all 20 settings pages. Computes the Spatie cache key correctly (`{prefix}.settings.{fqcn}`) and calls `Cache::store(config('settings.cache.store'))->forget($key)`.

Why a trait: Spatie Settings stores every group under a cache key like `settings.settings.App\Settings\HeroSettings` (class-FQCN-based, not short group name). The original spec text suggested `Cache::forget('settings.hero')` which would miss the actual key. The trait centralises the correct key computation — all 20 pages inherit it with `use BustsSettingsCache;`.

Note: Spatie's `SavedSettings` event subscriber already re-populates the cache on save, so the forget call is belt-and-braces. We keep it so editors see changes even if the event listener is ever disabled, and to satisfy the spec's grep verification.

### Resources (15 total, `app/Filament/Resources/`)

All in group **Справочники**. Table is `->reorderable('sort')->defaultSort('sort')` where applicable; form ends with a `Toggle::make('is_active')`; `TernaryFilter::make('is_active')` in the filter bar.

| Sort | Resource | Model | Notable features |
|---|---|---|---|
| 10  | `NavItemResource` | `NavItem` | label + anchor + `is_external` toggle |
| 20  | `TickerItemResource` | `TickerItem` | single label (uppercase in output) |
| 30  | `AboutLedgerRowResource` | `AboutLedgerRow` | `code` + 2 RichEditor HTML fields |
| 40  | `MetricTileResource` | `MetricTile` | `value_html` / `caption_html` with helper text explaining `<em>`, `<sup>`, `<span>` |
| 50  | `ServiceResource` | `Service` | `Repeater::make('spec_rows')` (array of `{k, v_html}`), `is_featured` toggle |
| 60  | `BitrixFeatureResource` | `BitrixFeature` | A.01-A.04 number + rich title + subtitle |
| 70  | `BitrixMockColumnResource` | `BitrixMockColumn` | **+ `CardsRelationManager`** — inline CRUD of `BitrixMockCard` with `Select` for `accent` (signal/ink/green) |
| 80  | `IndustryResource` | `Industry` | table row: number + title HTML + class label + caption |
| 90  | `WasteTypeResource` | `WasteType` | **media: `SpatieMediaLibraryFileUpload::make('image')`** with `imageEditor()` and webp conversion; `is_hazard` toggle color-codes the class badge |
| 100 | `ProcessStepResource` | `ProcessStep` | 5 fields; simple table |
| 110 | `PlanResource` | `Plan` | `Repeater::make('features')` with **hydrate/dehydrate** to flatten between DB's flat string array and the repeater's `['text' => ...]` shape; `is_highlighted` toggle |
| 120 | `RegionResource` | `Region` | number + UPPERCASE name |
| 130 | `MapPinResource` | `MapPin` | `Select::make('position_class')` restricted to `c1` … `c5` with human-readable CSS positions |
| 140 | `FooterColumnResource` | `FooterColumn` | **+ `LinksRelationManager`** — inline CRUD of `FooterLink` with `is_external` toggle |
| 150 | `SiteAssetResource` | `SiteAsset` | **media: `SpatieMediaLibraryFileUpload`**, `canCreate()` and `canDelete()` both return `false` — keys are fixed at seed time, editors only upload/replace images; `key` field is `disabled()` on edit |

Each resource has its own `Pages/List{X}`, `Pages/Create{X}`, `Pages/Edit{X}` trio (except `SiteAssetResource` which has only List + Edit, since creation is disabled).

### Relation managers (2 total)

- `app/Filament/Resources/BitrixMockColumnResource/RelationManagers/CardsRelationManager.php` — attached to `BitrixMockColumnResource::getRelations()`
- `app/Filament/Resources/FooterColumnResource/RelationManagers/LinksRelationManager.php` — attached to `FooterColumnResource::getRelations()`

Both managers expose reorder-by-sort, `CreateAction` in the header, `EditAction` + `DeleteAction` per row, and `DeleteBulkAction`.

### ContactRequestResource — deferred to Phase 5

Per spec. Leaves the **Заявки** group empty until Phase 5 creates `ContactRequestResource` (sort 10).

## Verification

| Check | Result |
|---|---|
| `php artisan route:list --name=filament.admin.pages` — count settings pages | ✅ 20 `manage-*` routes |
| `php artisan route:list --name=filament.admin.resources` — count resource `.index` | ✅ 15 resource index routes |
| `GET /admin` | ✅ `HTTP/1.1 302 Found` (redirect to login) |
| `GET /admin/login` | ✅ `HTTP/1.1 200 OK`, 43,529 bytes |
| Login HTML contains `lang="ru"` + Filament Russian translations | ✅ |
| Login HTML contains 2× "Криптон" (brand name) | ✅ |
| CSS primary colour = Orange (tokens `primary-50` … `primary-900` present in rendered CSS) | ✅ |
| `Filament::getPanel('admin')->getPages()` count | ✅ 21 (20 settings + `Dashboard`) |
| `Filament::getPanel('admin')->getResources()` count | ✅ 15 |
| `Filament::getPanel('admin')->getNavigationGroups()` | ✅ Контент / Справочники / Заявки / Настройки |
| All 20 ManageXxxSettings classes instantiate without error | ✅ |
| All 15 Resource classes report correct `getModel()` + `getNavigationGroup()` + `getNavigationSort()` | ✅ |
| `User::canAccessPanel()` returns `true` | ✅ |
| `Cache::store('file')->has('settings.settings.App\Settings\HeroSettings')` returns HIT after reading settings | ✅ |
| Same key returns `miss` after invoking `ManageHeroSettings::afterSave()` | ✅ (cache-bust proven via reflection) |
| `grep -rE "Cache::forget|BustsSettingsCache" app/Filament/` — all 20 settings pages covered | ✅ 22 files (trait + 20 pages + 1 Resource helper method) |
| `GET /` (public home) still renders `HTTP 200 OK` after admin additions | ✅ |

## Deviations from plan

1. **`Cache::forget('settings.{group}')` → shared trait**: the plan's one-liner key `settings.hero` does not match Spatie's actual cache key format `{prefix}.settings.{fqcn}`. Replaced per-page `afterSave()` bodies with a `BustsSettingsCache` trait that computes the correct key via `config('settings.cache.prefix')` + the class's `getSettings()` helper. Verified working: the cache-bust now actually evicts the entry.
2. **No explicit `->plugins([...])`** in `AdminPanelProvider`: `SettingsPage` and the media form/table components are provided by the package service providers — no plugin registration is needed (and the named `SpatieLaravelSettingsPlugin` class does not exist, only a `*ServiceProvider`). Verified pages/resources still discoverable.
3. **`Repeater` for `Plan::features`**: the JSON column stores a flat array of strings, but Filament's `Repeater` (even in `->simple()` mode) persists a nested shape. Added `afterStateHydrated` and `dehydrateStateUsing` on the Repeater to convert between the two forms cleanly. Public-site partial unchanged.
4. **`SpatieMediaLibraryFileUpload::collection('image')`**: the Phase 2 `WasteType` model uses the `'image'` collection name, not `'waste_fig'` as the Phase 4 spec mentioned. Adjusted to match the actual model.
5. **`SiteAssetResource` is upload-only**: `canCreate()` and `canDelete()` both return `false` because the 5 asset keys (hero_bg, about_archive, quote_reviewer, favicon, og_image) are fixed by the homepage partials. Editors upload files against existing rows; adding new keys would require code changes to display them.
6. **`FilamentInfoWidget` removed**: it shows Filament's own branding on the dashboard. `AccountWidget` (the logged-in user card) is kept.
7. **IBM Plex Sans on admin**: added `->font('IBM Plex Sans', provider: GoogleFontProvider::class)` so the admin typeface matches the site.

## Files created in Phase 4

```
app/Filament/
├── Concerns/
│   └── BustsSettingsCache.php                         (1 trait)
├── Pages/                                             (20 files)
│   ├── ManageGeneralSettings.php
│   ├── ManageDesignSettings.php
│   ├── ManageTopStripSettings.php
│   ├── ManageNavSettings.php
│   ├── ManageHeroSettings.php
│   ├── ManageAboutSettings.php
│   ├── ManageMetricsSettings.php
│   ├── ManageServicesSectionSettings.php
│   ├── ManageBitrixSettings.php
│   ├── ManageIndustriesSectionSettings.php
│   ├── ManageWasteSectionSettings.php
│   ├── ManageProcessSectionSettings.php
│   ├── ManagePlansSectionSettings.php
│   ├── ManageCoverageSettings.php
│   ├── ManageQuoteSettings.php
│   ├── ManageCtaBandSettings.php
│   ├── ManageContactSettings.php
│   ├── ManageFooterSettings.php
│   ├── ManageLegalSettings.php
│   └── ManageIntegrationSettings.php
└── Resources/                                         (15 resources)
    ├── NavItemResource.php + Pages/{List,Create,Edit}NavItem.php
    ├── TickerItemResource.php + Pages/{List,Create,Edit}TickerItem.php
    ├── AboutLedgerRowResource.php + Pages/{List,Create,Edit}AboutLedgerRow.php
    ├── MetricTileResource.php + Pages/{List,Create,Edit}MetricTile.php
    ├── ServiceResource.php + Pages/{List,Create,Edit}Service.php
    ├── BitrixFeatureResource.php + Pages/{List,Create,Edit}BitrixFeature.php
    ├── BitrixMockColumnResource.php
    │   ├── Pages/{List,Create,Edit}BitrixMockColumn.php
    │   └── RelationManagers/CardsRelationManager.php
    ├── IndustryResource.php + Pages/{List,Create,Edit}Industry.php
    ├── WasteTypeResource.php + Pages/{List,Create,Edit}WasteType.php
    ├── ProcessStepResource.php + Pages/{List,Create,Edit}ProcessStep.php
    ├── PlanResource.php + Pages/{List,Create,Edit}Plan.php
    ├── RegionResource.php + Pages/{List,Create,Edit}Region.php
    ├── MapPinResource.php + Pages/{List,Create,Edit}MapPin.php
    ├── FooterColumnResource.php
    │   ├── Pages/{List,Create,Edit}FooterColumn.php
    │   └── RelationManagers/LinksRelationManager.php
    └── SiteAssetResource.php + Pages/{List,Edit}SiteAsset.php
```

Modified: `app/Models/User.php` (added `FilamentUser` contract), `app/Providers/Filament/AdminPanelProvider.php` (brand / color / font / navigation groups).

Total: 1 trait + 20 Settings pages + 15 Resources + 44 Resource Pages + 2 Relation managers = **82 new PHP files**.

## Next — Phase 5

Port the contact form:
- Livewire component with honeypot, rate-limit, and Cloudflare Turnstile server-side verification
- `SubmitContactRequestAction` writing a `ContactRequest` row and firing a queued `NewContactRequestMail`
- `ContactRequestResource` in the **Заявки** group (sort 10) with status enum badge (New / Handled), filterable table, and inline status-toggle action
- Replace `resources/views/partials/contact.blade.php`'s static form with `<livewire:contact-form />`
