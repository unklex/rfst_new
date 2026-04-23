# Phase 3 — Complete

**Status**: ✅ Done — 2026-04-22
**Time**: ~40 min

## What landed

### CSS (verbatim port + form styles)

- `resources/css/app.css` — 493 lines pasted verbatim from `index-v2.html` lines 20-512, with:
  - `.tweaks{...}` block (lines 514-525) removed — replaced by `DesignSettings` in admin
  - `.wcard .fig picture, .wcard .fig img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover }` added so `<x-waste-fig>`'s uploaded image overlays the striped fallback pattern
  - Small form-feedback block appended (honeypot off-screen, `.field-err`, `.is-invalid`, `.form-success`) — matches the paper/ink palette. Rosecology's `app.css` was Tailwind-only with no reusable form styles, so the block was written fresh to plug into the Phase 5 Livewire form without restyling.

### JS

- `resources/js/app.js` — bootstrap import + smooth-scroll for `a[href^="#"]` (ported from `index-v2.html` lines 1232-1238). The tweaks postMessage block (1240-1265) is intentionally omitted — tweaks now live server-side in `DesignSettings`.

### Route + controller

- `routes/web.php` — `Route::get('/', HomeController::class)->name('home');`
- `app/Http/Controllers/HomeController.php` — `__invoke()` returns `view('pages.home')`. No eager data preload: partials resolve settings via `app(...)` and models via `::ordered()->active()->get()`; settings cache covers the read cost.

### Root layout

- `resources/views/layouts/app.blade.php` — `<!doctype html>` with `<html lang="ru">`, IBM Plex font preconnect, full SEO head (meta description, Open Graph, Twitter Card), conditional favicon + og:image from `SiteAsset`, conditional Turnstile `<script>` (only if `IntegrationSettings::turnstile_site_key` is set), `<body>` carries `data-signal` / `data-paper` / `data-head_weight` from `DesignSettings`, `@vite`, `@livewireStyles`, `@livewireScripts`.

### Components

- `resources/views/components/section-head.blade.php` — reusable `.sec-head` pattern (idx/kicker + h2 + note), accepts `$idx`, `$kicker`, `$headingHtml`, `$noteHtml`. Used by 6 partials: about, services, industries, waste, process, plans.
- `resources/views/components/waste-fig.blade.php` — renders `<picture><img>` from `getFirstMediaUrl('image', 'webp')` when an image is uploaded, else falls back to the design's `<div class="gly">{{ $wasteType->glyph }}</div>` letter.

### Home view + 17 partials

- `resources/views/pages/home.blade.php` — `@extends('layouts.app')`, includes the 17 partials in design order.

| Partial | Design lines | Consumes |
|---|---|---|
| `strip.blade.php` | 531-544 | `TopStripSettings` |
| `nav.blade.php` | 547-567 | `GeneralSettings`, `NavSettings`, `NavItem::ordered()->active()` |
| `hero.blade.php` | 570-621 | `HeroSettings` |
| `ticker.blade.php` | 624-651 | `TickerItem` — rendered **twice** inside `.track` (marquee loop) |
| `about.blade.php` | 654-681 | `AboutSettings`, `AboutLedgerRow`, `SiteAsset:about_archive` (inline `background-image` when uploaded) |
| `metrics.blade.php` | 684-713 | `MetricsSettings`, `MetricTile` |
| `services.blade.php` | 716-767 | `ServicesSectionSettings`, `Service` (`.featured` when `is_featured`) |
| `bitrix.blade.php` | 770-817 | `BitrixSettings`, `BitrixFeature`, `BitrixMockColumn::with('cards')`; accent → class map: `signal=''`, `ink='i'`, `green='g'` |
| `industries.blade.php` | 820-880 | `IndustriesSectionSettings`, `Industry` |
| `waste.blade.php` | 883-952 | `WasteSectionSettings`, `WasteType` + `<x-waste-fig>` |
| `process.blade.php` | 955-998 | `ProcessSectionSettings`, `ProcessStep` |
| `plans.blade.php` | 1001-1052 | `PlansSectionSettings`, `Plan` (`.hl` when `is_highlighted`) |
| `coverage.blade.php` | 1055-1084 | `CoverageSettings`, `Region`, `MapPin` |
| `quote.blade.php` | 1087-1106 | `QuoteSettings` |
| `cta-band.blade.php` | 1109-1118 | `CtaBandSettings` |
| `contact.blade.php` | 1121-1158 | `ContactSettings` — static prototype form (Phase 5 swaps in `<livewire:contact-form />`) |
| `footer.blade.php` | 1161-1214 | `FooterSettings`, `FooterColumn::with('links')`, `GeneralSettings`; massive wordmark dynamically wraps the configured italic char in `<em>` via `mb_strpos` |

## Verification

| Check | Result |
|---|---|
| `npm run build` | ✅ Vite builds manifest + 37.9 KB CSS + 38.8 KB JS in ~175 ms |
| `php artisan serve` → `GET /` | ✅ `HTTP 200 OK`, 47,619 bytes |
| `<html lang="ru">` | ✅ |
| `<body data-signal="hazard" data-paper="bone" data-head_weight="serif">` | ✅ (mirrors seed defaults) |
| View-source: `<title>Криптон — Индустриальная экология и Битрикс24</title>` | ✅ |
| View-source: `meta name="description"` with full lede | ✅ |
| View-source: 4 Open Graph + 3 Twitter Card meta tags | ✅ |
| View-source: favicon `<link>` — absent when no `SiteAsset::favicon` media | ✅ (conditional behaves correctly) |
| View-source: Turnstile `<script>` — absent when `turnstile_site_key` is null | ✅ |
| CSS + JS bundles served from `/build/assets/*` | ✅ both 200 OK |
| `data-paper="noir"` via `tinker + Cache::flush()` | ✅ whole site flips to dark palette on next request |
| Reverting paper → `bone` | ✅ site restored |
| Marquee content doubled (24 `<span>` total in ticker — 12 labels × 2 passes + 12 diamonds × 2) | ✅ |
| Per-section element counts | 8 wcards, 6 industry rows, 4 metric tiles, 3 svc (1 featured), 3 plan (1 `hl`), 5 proc, 10 region, 5 map pins, 3 footer cols, 7 nav items — all match seeds |
| Hero card kicker `<b>§ 001</b> · ...` | ✅ preg_replace wraps the `§` prefix in `<b>` |
| Footer massive wordmark `Крипт<em>о</em>н` | ✅ `mb_strpos` correctly locates the italic char |
| Contact form renders with all 4 `§` labels from `ContactSettings` | ✅ |

## Deviations from plan

1. **Partial count**: spec text says "18 Blade partials" in one place but the table lists 17 (strip → footer). Went with 17 — matches both the table and `home.blade.php`'s `@include` count.
2. **Rosecology form styles**: spec said "grep for `.field-err`, `.honeypot`, `.form-success`" and append. Rosecology's `resources/css/app.css` is 3 lines of Tailwind directives — no form styles to port. Wrote fresh equivalents sized to the crypton palette. Phase 5 (Livewire form) will consume them.
3. **Waste fallback image coverage**: added one CSS rule (`.wcard .fig picture, .wcard .fig img`) absolute-position the uploaded image inside the striped `.fig` box. Prototype had no image variant — this is the bridge for Phase 6 uploads.
4. **Hero kicker bold**: the design marks `§ 001` with `<b>` and surrounds `·` with `&nbsp;`. Seed stored the kicker as a plain `'§ 001 · на рынке с 2014 г.'` string. The partial uses `preg_replace('/^(.*?)·/u', '<b>$1</b>·', ...)` to reconstruct the bold prefix at render time. No settings schema change required.
5. **About photo background**: when a `SiteAsset::about_archive` image is uploaded, the partial inlines `style="background-image:url(...)"` on `.about-photo`. With no image, the original striped gradient from `repeating-linear-gradient` carries through unchanged.
6. **Footer massive wordmark italic char**: settings store `massive_wordmark` + `massive_italic_char` separately. Blade computes the position at render time and wraps the matching glyph in `<em>` — survives rename of the company or swap of the accent character.

## Files created in Phase 3

```
app/Http/Controllers/
  HomeController.php                                 (invokable — returns view)

resources/css/
  app.css                                            (~494 lines — verbatim port + form feedback)

resources/js/
  app.js                                             (bootstrap + smooth scroll)

resources/views/
  layouts/app.blade.php                              (root HTML with SEO head, vite, livewire)
  pages/home.blade.php                               (extends layout, includes 17 partials)
  components/
    section-head.blade.php                           (idx / kicker / h2 / note)
    waste-fig.blade.php                              (picture OR gly fallback)
  partials/
    strip.blade.php       nav.blade.php
    hero.blade.php        ticker.blade.php
    about.blade.php       metrics.blade.php
    services.blade.php    bitrix.blade.php
    industries.blade.php  waste.blade.php
    process.blade.php     plans.blade.php
    coverage.blade.php    quote.blade.php
    cta-band.blade.php    contact.blade.php
    footer.blade.php

routes/
  web.php                                            (overwritten — now maps / → HomeController)
```

## Known punch list for later phases

- The `index-v2.html` design has no explicit anchor on the hero section for the nav's `#top` link (first nav item anchors to `#top`). Phase 4's admin edit of `NavItem::first()` should rename this to `#hero` or the hero section should grow `id="top"`. Not breaking — just a dead scroll anchor right now.
- `data-paper="bone"` currently uses the CSS `:root` defaults (no dedicated `[data-paper="bone"]` selector exists). The 3 valid values are still `bone | fog | noir`, matching the seed enum. No code change needed.
- `partials/contact.blade.php` is the static prototype form — Phase 5 replaces it with `<livewire:contact-form />` and wires up honeypot + Turnstile + rate-limit + the action that writes `ContactRequest` and queues the Mailable.

## Next — Phase 4

Scaffold the Filament admin: 20 Settings pages (one per Settings class) + 16 Resources (one per content model, excluding `User` + `SiteAsset`-less primitives), each declaring `$navigationSort` so the admin menu mirrors the landing-page top-to-bottom flow. Wire `afterSave()` on every Settings page to `Cache::forget('settings.{group}')` so admin edits immediately reflect on `/`.
