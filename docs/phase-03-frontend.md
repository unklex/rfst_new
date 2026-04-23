# Phase 3 — Frontend Port

**Goal**: homepage at `/` is pixel-identical to `index-v2.html`, with every text fragment and image slot sourced from the Settings and models seeded in Phase 2. No admin UI yet — this phase validates that the data model correctly reproduces the design.

## Prerequisites

- Phase 2 complete: `migrate:fresh --seed` populates the full design copy
- `app(\App\Settings\HeroSettings::class)->headline_html` returns the Russian headline HTML

## Tasks

### 1. CSS port (verbatim)

Create `resources/css/app.css`:

1. Copy lines **19-526** from `index-v2.html` verbatim (the entire `<style>...</style>` block minus the opening/closing tags).
2. **Delete only lines 514-525** (the `.tweaks{...}` block) — the floating dev panel is replaced by `DesignSettings` in admin.
3. Append form-related styles from `C:\Projects\rosecology_new\app\resources\css\app.css`: grep for `.field-err`, `.form-success`, `.honeypot`, and field error states. These are the only additions — do not copy rosecology's page styles.

### 2. JS port

Create `resources/js/app.js`:

```js
import './bootstrap';

// Smooth scroll to anchors (ported from index-v2.html lines 1232-1238)
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        const id = a.getAttribute('href');
        if (id.length < 2) return;
        const el = document.querySelector(id);
        if (!el) return;
        e.preventDefault();
        window.scrollTo({ top: el.getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
    });
});
```

Do NOT port the `tweaks` panel postMessage block (lines 1240-1265) — the tweaks are now set server-side via `DesignSettings`.

### 3. Vite config

Edit `vite.config.js` to include the new CSS:

```js
input: ['resources/css/app.css', 'resources/js/app.js']
```

### 4. Route + controller

`routes/web.php`:

```php
Route::get('/', \App\Http\Controllers\HomeController::class)->name('home');
```

`app/Http/Controllers/HomeController.php` — `__invoke()` that returns `view('pages.home')`. All data is resolved inside partials via `app(...)` — no heavy controller preload, the settings cache handles repeat queries.

### 5. Root layout

`resources/views/layouts/app.blade.php`:

- `<!doctype html><html lang="ru">` with `<head>` mirroring index-v2.html lines 3-10 (fonts, preconnect)
- `<title>{{ app(\App\Settings\GeneralSettings::class)->site_name }} — {{ ...tagline }}</title>`
- `<meta name="description" content="{{ $general->meta_description }}">`
- Favicon from `SiteAsset` key `favicon` (webp variant) — fallback: no favicon
- Open Graph + Twitter Card meta tags from `SiteAsset::og_image` + `GeneralSettings::meta_description`
- Turnstile script + site key (from `IntegrationSettings::turnstile_site_key`), conditionally loaded
- `<body data-signal="{{ $design->signal }}" data-paper="{{ $design->paper }}" data-head_weight="{{ $design->head_weight }}">`
- `@vite(['resources/css/app.css', 'resources/js/app.js'])`
- `@yield('content')`
- `@livewireScripts` (for the contact form in Phase 5)

### 6. Home view

`resources/views/pages/home.blade.php`:

```blade
@extends('layouts.app')
@section('content')
  @include('partials.strip')
  @include('partials.nav')
  @include('partials.hero')
  @include('partials.ticker')
  @include('partials.about')
  @include('partials.metrics')
  @include('partials.services')
  @include('partials.bitrix')
  @include('partials.industries')
  @include('partials.waste')
  @include('partials.process')
  @include('partials.plans')
  @include('partials.coverage')
  @include('partials.quote')
  @include('partials.cta-band')
  @include('partials.contact')
  @include('partials.footer')
@endsection
```

### 7. Partials (18 files in `resources/views/partials/`)

Write in this order — run `php artisan serve` after each and visually diff against the prototype.

Each partial mirrors a marked `<!-- ====... ====== -->` block in `index-v2.html`. Markup is a line-for-line copy, with literals replaced by `{{ }}` (plain text) or `{!! !!}` (HTML).

| Partial | Design lines | Consumes |
|---|---|---|
| `strip.blade.php` | 531-544 | `TopStripSettings` |
| `nav.blade.php` | 547-567 | `GeneralSettings`, `NavSettings`, `NavItem::ordered()` |
| `hero.blade.php` | 570-621 | `HeroSettings`, `SiteAsset` (hero_bg) |
| `ticker.blade.php` | 624-651 | `TickerItem::ordered()` — render the collection twice inside `.track` for the marquee loop |
| `about.blade.php` | 654-681 | `AboutSettings`, `AboutLedgerRow::ordered()`, `LegalSettings`, `SiteAsset` (about_archive) |
| `metrics.blade.php` | 684-713 | `MetricsSettings`, `MetricTile::ordered()` |
| `services.blade.php` | 716-767 | `ServicesSectionSettings`, `Service::ordered()` (apply `.featured` class when `is_featured`) |
| `bitrix.blade.php` | 770-817 | `BitrixSettings`, `BitrixFeature::ordered()`, `BitrixMockColumn::ordered()->with('cards')` |
| `industries.blade.php` | 820-880 | `IndustriesSectionSettings`, `Industry::ordered()` |
| `waste.blade.php` | 883-952 | `WasteSectionSettings`, `WasteType::ordered()` + `<x-waste-fig>` component |
| `process.blade.php` | 955-998 | `ProcessSectionSettings`, `ProcessStep::ordered()` |
| `plans.blade.php` | 1001-1052 | `PlansSectionSettings`, `Plan::ordered()` (apply `.hl` class when `is_highlighted`) |
| `coverage.blade.php` | 1055-1084 | `CoverageSettings`, `Region::ordered()`, `MapPin::ordered()` |
| `quote.blade.php` | 1087-1106 | `QuoteSettings`, `SiteAsset` (quote_reviewer) |
| `cta-band.blade.php` | 1109-1118 | `CtaBandSettings` |
| `contact.blade.php` | 1121-1158 | `ContactSettings` — for now render the static HTML form from the prototype (Phase 5 replaces with `<livewire:contact-form />`) |
| `footer.blade.php` | 1161-1214 | `FooterSettings`, `FooterColumn::ordered()->with('links')`, `LegalSettings`, `GeneralSettings` |

### 8. Components

- `resources/views/components/section-head.blade.php` — the `.sec-head` pattern (idx/kicker + h2 + note). Accepts `$idx`, `$kicker`, `$headingHtml`, `$noteHtml`. Used by about/services/industries/waste/process/plans partials.
- `resources/views/components/waste-fig.blade.php` — renders `getFirstMediaUrl('waste_fig', 'webp')` as a `<picture>` when set, else the fallback `<div class="fig"><div class="gly">{{ $wasteType->glyph }}</div></div>`.

### 9. Build assets

```bash
npm install
npm run dev    # or npm run build for production-optimized
```

## Verification

- [ ] `php artisan serve` → `http://127.0.0.1:8000` renders the full page
- [ ] Visual diff against `index-v2.html` (open both side-by-side): every section present in the same order, every text fragment matches, every class name preserved
- [ ] Top strip blinking dot animates, tech ribbon marquee scrolls, compass needle swings (all CSS-driven — if missing, check `resources/css/app.css` paste completeness)
- [ ] Hover states on services cards, industry rows, bitrix feature list work
- [ ] Smooth scroll works for nav anchors (`#about`, `#services`, etc.)
- [ ] `data-signal`, `data-paper`, `data-head_weight` attributes present on `<body>` (inspect element)
- [ ] Change `DesignSettings::paper` to `noir` via tinker → reload → whole site flips to dark palette
- [ ] View source shows meta description, open graph tags, favicon link (from settings)
- [ ] Turnstile script tag is absent when `IntegrationSettings::turnstile_site_key` is empty
- [ ] Browser console clean (no 404s, no JS errors)

## Common pitfalls

- **Fonts not loading**: confirm the `<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+..."` line from prototype line 9 is in the layout head.
- **CSS grid columns collapse**: almost always a missing `.frame` wrapper or `.cols` class. Diff the offending section against the HTML line-by-line.
- **`em` italics not orange**: the `em` color is set by `color: var(--signal)` inside each section. Confirm the CSS block for that section was included in the paste.
- **Marquee doesn't animate**: render `TickerItem` collection twice in `.track` — the keyframe animates from 0 to -50%, needs doubled content.
- **Compass needle static**: check `@keyframes swing` was preserved in CSS.
- **Settings cache showing stale data**: run `php artisan cache:clear` after tweaking Phase 2 seeders.

## Next

Phase 4 wires up Filament so content editors can flip any of these values without tinker. Keep `partials/contact.blade.php` with the static form for now — Livewire is Phase 5.
