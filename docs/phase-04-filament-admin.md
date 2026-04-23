# Phase 4 — Filament Admin

**Goal**: a production admin panel where an editor can log in, flip any text or image, drag to reorder repeated items, and see the change immediately on the public homepage. Every Settings page and Resource has explicit `$navigationSort` so the menu mirrors the landing-page flow top-to-bottom.

## Prerequisites

- Phase 3 complete: homepage renders identically to design
- Phase 1's `php artisan filament:install --panels` has already generated a skeleton `AdminPanelProvider`

## Reference

- `C:\Projects\rosecology_new\app\app\Providers\Filament\AdminPanelProvider.php` — template
- `C:\Projects\rosecology_new\app\app\Filament\Resources\ContactRequestResource.php` — full Filament form/table example (port in Phase 5, but study now)
- `C:\Projects\rosecology_new\app\app\Filament\Pages\ManageSiteSettings.php` — settings page template

## Tasks

### 1. Customize `AdminPanelProvider`

`app/Providers/Filament/AdminPanelProvider.php`:

- `->brandName('Криптон · Админка')`
- `->colors(['primary' => Color::Orange])` — matches the signal color
- `->navigationGroups(['Контент', 'Справочники', 'Заявки', 'Настройки'])` — order matters; this is also the user-visible sidebar order
- `->plugins([SpatieLaravelSettingsPlugin::make(), SpatieLaravelMediaLibraryPlugin::make()])`
- `->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')`
- `->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')`
- `->login()` — stock login page is fine
- Profile + password reset routes off (single-tenant admin)

### 2. `User` implements `FilamentUser`

`app/Models/User.php`:

```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return true; // single-tenant — any authenticated user is admin
    }
}
```

### 3. Settings pages (20 files in `app/Filament/Pages/`)

Template (use `ManageGeneralSettings.php` as the exemplar):

```php
namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Pages\SettingsPage;
use Filament\Forms\Components\{TextInput, Textarea};
use Filament\Forms\Form;
use Illuminate\Support\Facades\Cache;

class ManageGeneralSettings extends SettingsPage
{
    protected static string $settings = GeneralSettings::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Общие';
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'Общие настройки';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('site_name')->required()->label('Название сайта'),
            TextInput::make('tagline')->label('Подзаголовок'),
            Textarea::make('meta_description')->rows(3)->label('Meta description (SEO)'),
            // ... rest of the properties
        ]);
    }

    protected function afterSave(): void
    {
        Cache::forget('settings.general');
    }
}
```

**Required on every Settings page**:

- `$navigationGroup = 'Настройки'`
- `$navigationSort` — see the range table below
- `afterSave()` that calls `Cache::forget('settings.{group}')`
- Russian labels on every field
- `RichEditor::class` for properties ending `_html` (with `->toolbarButtons(['bold', 'italic', 'link'])` to keep it tight)

Full $navigationSort allocation (`Настройки` group):

```
10  ManageGeneralSettings
20  ManageDesignSettings
30  ManageTopStripSettings
40  ManageNavSettings
50  ManageHeroSettings
60  ManageAboutSettings
70  ManageMetricsSettings
80  ManageServicesSectionSettings
90  ManageBitrixSettings
100 ManageIndustriesSectionSettings
110 ManageWasteSectionSettings
120 ManageProcessSectionSettings
130 ManagePlansSectionSettings
140 ManageCoverageSettings
150 ManageQuoteSettings
160 ManageCtaBandSettings
170 ManageContactSettings
180 ManageFooterSettings
190 ManageLegalSettings
200 ManageIntegrationSettings
```

For `ManageIntegrationSettings`, `turnstile_secret_key` uses `TextInput::password()->revealable()`.

For `ManageDesignSettings`, each property is `Select::options([...])` with Russian labels.

### 4. Resources (16 files in `app/Filament/Resources/`)

Template: any of rosecology's resources. Each resource needs:

- `$navigationGroup = 'Контент'` or `'Справочники'` per table below
- `$navigationSort` — see allocation
- `$navigationIcon`
- Table: columns including drag-reorder handle (`->reorderable('sort')`), active toggle, key preview column
- Form: all fields with Russian labels; `_html` fields as `RichEditor`; json fields as `KeyValue` or `Repeater`
- `is_active` filter

$navigationSort allocation (`Справочники` group):

```
10  NavItemResource
20  TickerItemResource
30  AboutLedgerRowResource
40  MetricTileResource
50  ServiceResource
60  BitrixFeatureResource
70  BitrixMockColumnResource
80  IndustryResource
90  WasteTypeResource
100 ProcessStepResource
110 PlanResource
120 RegionResource
130 MapPinResource
140 FooterColumnResource
150 SiteAssetResource
```

### 5. Special resource configurations

- **`ServiceResource`**: `KeyValue::make('spec_rows')->keyLabel('Ключ')->valueLabel('Значение (HTML разрешён)')` — 3 entries. Toggle `is_featured`.
- **`PlanResource`**: `Repeater::make('features')->simple(TextInput::make('text'))->grid(1)` for the bullet list. Toggle `is_highlighted`.
- **`BitrixMockColumnResource`**: has a `RelationManager` for mock cards (`BitrixMockCardsRelationManager`) — allows managing the kanban-column contents inline.
- **`FooterColumnResource`**: has a `RelationManager` for `FooterLinksRelationManager` — edit the links under each footer heading.
- **`WasteTypeResource`**: `SpatieMediaLibraryFileUpload::make('image')->collection('waste_fig')->image()->imageEditor()->conversion('webp')`.
- **`SiteAssetResource`**: list each key as a row (5 rows, seeded in Phase 2 or creatable here). One `SpatieMediaLibraryFileUpload` per row. Include image preview in table.

### 6. ContactRequestResource — deferred

Leave ContactRequestResource for Phase 5 where it's created alongside the contact-form flow. It goes in the `Заявки` group with `$navigationSort = 10`.

### 7. Login page branding (optional polish)

In `AdminPanelProvider::boot()`, customize the login view's brand logo to match the site's mark (the "К" box from hero).

## Verification

- [ ] Visit `http://127.0.0.1:8000/admin` → redirects to login
- [ ] Log in with `ADMIN_EMAIL`/`ADMIN_PASSWORD` → dashboard loads
- [ ] Sidebar groups appear in order: **Контент** → **Справочники** → **Заявки** (empty for now) → **Настройки**
- [ ] Inside **Настройки**, pages appear in landing-page order: Общие → Палитра → Верхняя полоса → Меню → Hero → О компании → Метрики → ... → Интеграции
- [ ] Inside **Справочники**, resources appear: Меню → Бегущая строка → ... → Медиа-файлы
- [ ] Entire UI in Russian (sidebar labels, form labels, save buttons, pagination, validation messages)
- [ ] Open "Hero" settings page → change `headline_html` → save → reload `/` → headline updates **without** running `cache:clear`
- [ ] Open "Услуги" (Services) resource → drag a row to reorder → reload `/` → order changes
- [ ] Toggle `is_featured` on a different service → orange `.featured` card moves
- [ ] Open "Палитра" (DesignSettings) → change `paper` to `noir` → save → reload `/` → whole site goes dark
- [ ] Upload an image to `SiteAsset::about_archive` via SiteAssetResource → confirm file in `storage/app/public/*/conversions/*-webp.webp` → reload `/` → about section uses the image
- [ ] `Cache::get('settings.hero')` returns a hit after first page view; returns null after `afterSave()`
- [ ] `grep -r "Cache::forget" app/Filament/Pages/` returns a hit for every Settings page

## Common pitfalls

- **Sidebar items in wrong order**: missing `$navigationSort` on a page/resource. Filament falls back to alphabetical.
- **"Undefined setting property" error on save**: the setting class doesn't declare the property, or the seed migration didn't add it. Run `php artisan migrate:fresh --seed` after adding.
- **`afterSave` doesn't exist**: you're extending `Resource` instead of `SettingsPage`, or Filament version mismatch. Settings pages inherit from `Filament\Pages\SettingsPage` only.
- **Homepage doesn't update after save**: `afterSave()` missing the `Cache::forget` call. Add it for every group.
- **RichEditor produces `<p>` tags that break design**: use `->toolbarButtons(['bold', 'italic', 'link'])` only, and strip wrapping `<p>` on save with `->dehydrateStateUsing(fn($s) => trim(preg_replace('/^<p>|<\/p>$/', '', $s)))` if needed.
- **FileUpload doesn't trigger webp conversion**: confirm model implements `HasMedia` and `registerMediaConversions` is declared — see Phase 2 § E.
- **Login loops**: `User::canAccessPanel()` returns false. Double-check `implements FilamentUser`.

## Next

Phase 5 ports the contact form from rosecology — Livewire component + action + Turnstile + Mailable + Filament resource for incoming leads.
