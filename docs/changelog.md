# Changelog

All notable changes to the Crypton Site project.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/). Dates in `YYYY-MM-DD`.

## [Unreleased]

### Planned
- Phase 7 — Production build + verification

---

## [0.7.1] — 2026-04-27 — Hotfix: section-head HTML props + Plan repeater shape

### Fixed
- **Six landing-page partials** (`about`, `services`, `industries`, `waste`, `process`, `plans`) passed HTML-bearing settings to `<x-section-head>` via `heading-html="{{ $s->section_heading_html }}"`. Blade's `{{ }}` HTML-escapes the value before it reaches the component, so the inner `{!! !!}` rendered an already-encoded string and `<em>`, `<b>`, `<br>` tags appeared as literal text on the page (e.g. the §01 about heading showed `<em>управлению отходами</em>` and the legal block showed `<b>ООО «Криптон»</b>`). Switched all 12 attribute bindings (`heading-html` + `note-html`/`legal_block_html` × 6 partials) to the `:heading-html="..."` PHP-expression syntax so the raw HTML is forwarded to the component unchanged. Commit `534cf79`
- **`plans.blade.php` 500 on production** — `htmlspecialchars(): Argument #1 ($string) must be of type string, array given`. Cause: the `PlanResource` Filament `Repeater` for `features` has a single `text` subfield with a `dehydrateStateUsing` flattener, but on certain admin save paths the dehydrator doesn't fire and the DB ends up with `[{"text":"Foo"}, ...]` instead of the flat `["Foo", ...]` the blade expects. Two-layer fix: (a) blade now tolerates both shapes via `{{ is_array($f) ? ($f['text'] ?? '') : $f }}` so the page can never 500 from a bad row; (b) `EditPlan::mutateFormDataBeforeSave` and `CreatePlan::mutateFormDataBeforeCreate` flatten `features` one more time before the model is touched, belt-and-suspenders for the dehydrator path. Commit `ccb7cf6`

### Verified
- `https://rf-st.ru/` returns `HTTP/2 200` after deploying both commits + `php artisan view:clear && php artisan view:cache` on Beget
- §01 about heading renders italics correctly; legal block on the right renders `<b>` + `<br>` correctly
- Plans section bullets render without 500 regardless of whether existing rows hold flat strings or wrapped `{text: ...}` arrays
- Future admin saves of a Plan re-flatten `features` to plain strings before persistence (verified in form lifecycle hooks)

### Note
- Filament `RichEditor` wraps content in `<p>...</p>` on save, which means future admin edits of `section_heading_html` will produce `<h2><p>...</p></h2>` — invalid HTML but not a 500. Cosmetic cleanup deferred until someone actually edits a heading via the panel and notices the layout shift
- Existing DB rows with the wrapped `[{"text": ...}]` shape were left in place — the defensive blade renders them correctly. A one-shot tinker normaliser is documented in the deploy notes if a clean migration is wanted later

---

## [0.7.0] — 2026-04-23 — FastAPI forwarder + Sentry (supersedes Phase 5's "DB+mail only" decision)

### Added
- `sentry/sentry-laravel ^4` dependency (auto-discovered); `config/sentry.php` published
- `bootstrap/app.php` — `\Sentry\Laravel\Integration::handles($exceptions)` inside `withExceptions()` so every unhandled exception reports to Sentry automatically
- `app/Providers/AppServiceProvider::register()` — runtime override of `config('sentry.dsn')` from `IntegrationSettings::sentry_dsn` (admin UI), with `env('SENTRY_LARAVEL_DSN')` as fallback and a try/catch around the DB read for pre-migrate/fresh-install safety
- `app/Jobs/ForwardLeadToFastApiJob.php` — port of rosecology's forwarder, `service_id` dropped; `source` derived from `parse_url(config('app.url'), PHP_URL_HOST)`; payload includes `lead` (name/phone/email/message/consent hash+accepted_at) and `tracking` (5 UTM fields + referer + landing + ip_hash + user_agent) plus `submitted_at`. Bearer token auth via `IntegrationSettings::fastapi_auth_token` when set. `handle()` is exception-safe: network errors and non-2xx responses flip the lead to `Failed` and `report()` to Sentry — they never bubble up so the Livewire submit flow stays clean even under `QUEUE_CONNECTION=sync`. Async retries (30s/2m/10m backoff) still activate if the queue driver is later switched to `database`/`redis`
- `app/Filament/Resources/ContactRequestResource.php` — new table columns: `fastapi_status_code` (badge color by HTTP status), `external_id` (copyable, hidden by default), `forwarded_at` (hidden by default). New row action **«Отправить в FastAPI»** — visible only for `new`/`failed` leads when `fastapi_lead_url` is set, dispatches the forwarder via `afterResponse()`. New infolist section **FastAPI / CRM** with status badge + external ID + forwarded_at + KeyValueEntry of the full upstream response
- `app/Filament/Pages/ManageIntegrationSettings.php` — two new sections: **FastAPI — приёмник заявок** (URL + encrypted Bearer token) and **Sentry (мониторинг ошибок)** (DSN override with Russian helper text explaining the env fallback chain)
- `database/migrations/2026_04_23_120000_add_fastapi_columns_to_contact_requests.php` — adds `fastapi_status_code` (unsignedSmallInteger, nullable), `fastapi_response` (json, nullable), `forwarded_at` (timestamp, nullable), `external_id` (string 64, nullable, indexed)
- `database/settings/2026_04_23_120100_add_fastapi_and_sentry_to_integrations.php` — seeds `fastapi_lead_url`, `fastapi_auth_token` (encrypted), `sentry_dsn` as null defaults
- `.env.example` — `SENTRY_LARAVEL_DSN=` placeholder + `SENTRY_ENVIRONMENT=production`, with Russian comments clarifying the Settings-first priority chain
- `docs/core_decisions.md` — new dated entry **"2026-04-23 — FastAPI forwarder restored + Sentry added"**, supersedes the 2026-04-22 "no external CRM push" decision (kept in history for the reasoning trail)

### Changed
- `app/Enums/ContactRequestStatus.php` — two new cases: `Forwarded` (label «Отправлена», color `info`), `Failed` (label «Ошибка», color `danger`). Existing `New` and `Handled` unchanged
- `app/Models/ContactRequest.php` — fillable + casts extended with `fastapi_status_code`, `fastapi_response` (array), `forwarded_at` (datetime), `external_id`
- `app/Settings/IntegrationSettings.php` — adds `fastapi_lead_url`, `fastapi_auth_token` (encrypted), `sentry_dsn` as public nullable strings. `encrypted()` list now returns `['turnstile_secret_key', 'fastapi_auth_token']`
- `app/Actions/Contact/SubmitContactRequestAction.php` — after the Mail dispatch, if `fastapi_lead_url` is non-empty, dispatches `ForwardLeadToFastApiJob($lead->id)` via `->afterResponse()`. Preserves the exact same user-facing UX (success panel flips instantly) — the POST fires in the after-response hook, invisible to the submitter

### Verified
- `php -l` on all 11 new/changed PHP files: no syntax errors
- `composer require sentry/sentry-laravel:^4 --no-interaction` — installs cleanly; `vendor:publish --provider='Sentry\Laravel\ServiceProvider'` lands `config/sentry.php`
- `php artisan migrate --force` — 2 new migrations apply cleanly (33 ms schema, 27 ms settings)
- Schema check: 4 new columns present on `contact_requests` (`fastapi_status_code`, `fastapi_response`, `forwarded_at`, `external_id`)
- Settings check: `integrations.fastapi_lead_url`, `integrations.fastapi_auth_token`, `integrations.sentry_dsn` present in the `settings` table
- Enum check: 4 cases, each resolves to its Russian label + Filament color
- Forwarder happy path (via `Http::fake` → 201 with `{external_id: 'CRM-42'}`): lead's `status` flips `new → forwarded`, `fastapi_status_code = 201`, `forwarded_at` set, `external_id = 'CRM-42'`. Bearer token present in outbound `Authorization` header. Payload includes `source` (from `APP_URL` host), full lead block with consent hash + accepted_at, and `tracking` block with `utm_source`, `utm_campaign`, `utm_content`, `utm_term`, `utm_medium`, `referer`, `landing_url`, `ip_hash`, `user_agent`
- Forwarder failure path (via `Http::fake` → 500): lead's `status` flips `new → failed`, `fastapi_status_code = 500`, `fastapi_response` contains the upstream body, `forwarded_at` stays null. No exception propagates
- Forwarder with no URL configured → early return, lead stays `new` — no side effects
- `ContactRequestResource::table` shows 3 new columns; «Отправить в FastAPI» row action is visible on `new`/`failed` leads with a URL configured, hidden otherwise
- `ManageIntegrationSettings` form renders the new FastAPI + Sentry sections without errors

### Note
- `QUEUE_CONNECTION=sync` + `dispatch()->afterResponse()` is the combo that lets this work on Beget without a queue worker — the forwarder POST runs inside the same PHP-FPM process, *after* the response is flushed to the browser, using Laravel's `app->terminating()` hook. Zero cron setup needed
- Sentry SDK is a no-op when DSN is null — no network calls, no exceptions, no latency. Safe to deploy before pasting a real DSN into the admin UI

## [0.6.0] — 2026-04-23 — Phase 6: media pipeline + SEO tags

### Added
- `resources/views/layouts/app.blade.php` — `<link rel="canonical">` bound to `url()->current()`; `<meta property="og:image:width|height|alt">`; `<meta name="twitter:image:alt">`; `<link rel="apple-touch-icon">` (falls back to the uploaded favicon's original file); favicon + apple-touch-icon carry `?v={updated_at-timestamp}` cache-busters; `<link rel="icon" href="/favicon.ico">` fallback when no `SiteAsset::favicon` media is uploaded; optional Yandex.Metrika counter (`ym(<id>, "init", { clickmap:true, trackLinks:true, accurateTrackBounce:true })` + `<noscript>` pixel), rendered only when `IntegrationSettings::yandex_metrika_id` matches `/^\d{5,10}$/`
- `resources/views/partials/quote.blade.php` — optional `<picture><source type="image/webp">…<img loading="lazy" decoding="async">` headshot in the left `.quote .side`, rendered only when `SiteAsset::quote_reviewer` has uploaded media. Alt text falls back to `QuoteSettings::reviewer_name`. Falls back to the Phase 3 text-only rendering when no media is present
- `public/robots.txt` — replaced stock `Disallow:` with explicit `Allow: /`, disallow `/admin` + `/livewire`, commented `Sitemap:` placeholder

### Changed
- `app/Models/SiteAsset.php` — added `public bool $registerMediaConversionsUsingModelInstance = true;` so `registerMediaConversions()`'s `match ($this->key) { 'hero_bg', 'og_image' => 1920, 'about_archive' => 1280, 'quote_reviewer' => 480, 'favicon' => 180, default => 1280 }` + the favicon-skips-mobile guard actually see the parent model's attributes. Without the flag, Spatie invokes the method on a fresh, unhydrated instance, so `$this->key` is `null`: all conversions fall through to the 1280 default arm and favicon gets an unwanted 720px mobile variant. Verified by comparing `getimagesize()` on each conversion before/after the flag

### Verified
- GD / WebP: `print_r(gd_info())` shows `WebP Support => yes`
- Upload test: 3 JPGs via `$asset->addMedia(...)->preservingOriginal()->toMediaCollection('image')`
  - `hero_bg` → `phase6-hero-webp.webp` **1920×1080** + `phase6-hero-webp_mobile.webp` **720×405**
  - `favicon` → `phase6-favicon-webp.webp` **180×180** (no mobile variant — correct)
  - `og_image` → `phase6-og-webp.webp` **1920×1080** + `phase6-og-webp_mobile.webp` **720×405**
  - `quote_reviewer` → 480×480 webp
- `GET /` → `HTTP 200`, ~51.6 KB (up from 50.7 KB pre-media, delta explained by new meta/link tags)
- View-source: `<link rel="canonical">`, full OG image dims+alt, `<meta name="twitter:image:alt">`, favicon cache-bust `?v=<timestamp>`, apple-touch-icon present
- Quote reviewer: with upload, first `.quote .side` renders `<picture>` with `<source type="image/webp">` + `<img loading="lazy" decoding="async">` above the text; without upload, side column is text-only identical to Phase 3
- Yandex Metrika: `yandex_metrika_id = '12345678'` → counter snippet + `<noscript>` pixel render; `yandex_metrika_id = 'UA-123'` (non-numeric) → regex gate blocks, no snippet; `yandex_metrika_id = null` → no snippet
- `GET /robots.txt` → `HTTP 200`, 7 correct lines
- `php artisan migrate:fresh --seed` still works after the `$registerMediaConversionsUsingModelInstance` addition
- `npx vite build` → CSS 37.87 kB + JS 39.17 kB, 164 ms (no change — Phase 6 touched only Blade + PHP)

### Note
- Sitemap generation (`spatie/laravel-sitemap`) deferred until Phase 7 decides on a production URL — single-page site gives this feature low ROI in dev; `robots.txt` has a commented `Sitemap:` line ready to uncomment
- Image optimizers (`jpegoptim`/`pngquant`/`cwebp` native binaries) not added — conversions already work via GD's `quality(82)/(78)`; production host can add the `image_optimizers` block to `config/media-library.php` when binaries are available
- `hero.blade.php` + `about.blade.php` left untouched — neither has a design slot where a `<picture>` fits without restructuring the decorative CSS layers

## [0.5.0] — 2026-04-23 — Phase 5: contact form + lead pipeline

### Added
- `app/Actions/Contact/VerifyTurnstileAction.php` — server-side Cloudflare Turnstile verification. Reads `turnstile_secret_key` from `IntegrationSettings` at runtime (never `env()`). Empty secret → `true` (dev/off mode); network errors and non-200 responses → `false` with `Log::warning`; `success=false` → `false` with `Log::info` including Cloudflare `error-codes`
- `app/Actions/Contact/SubmitContactRequestAction.php` — invokable action: honeypot check → consent check → Turnstile verify → `ContactRequest::create(...)` (sha256 of `ContactSettings::personal_data_consent_text` into `consent_text_hash`, UTM parsed from landing URL, IP hashed with sha256, UA truncated to 512) → if `IntegrationSettings::notify_email` non-null, `Mail::to(...)->queue(new NewContactRequestMail($lead))`. Throws `\DomainException` with Russian copy for honeypot / consent / captcha failures
- `app/Mail/NewContactRequestMail.php` — queued Markdown mailable (`implements ShouldQueue`), subject `"Новая заявка с сайта — {name}"`, view `mail.new-contact-request`
- `resources/views/mail/new-contact-request.blade.php` — `mail::message` with 10-row table (name, phone, email, message, UTM JSON, referer, landing, IP hash, UA, created) + `mail::button` linking to `/admin/contact-requests/{id}`
- `app/Livewire/ContactForm.php` — component with `#[Validate]` attributes (Russian messages), honeypot short-circuit, 5-per-10-minute rate limit keyed by client IP, `#[On('turnstile-verified')]` listener, success-state flag
- `resources/views/livewire/contact-form.blade.php` — root `<div>` containing either `<form wire:submit.prevent="submit">` matching the prototype's `.contact form` layout (name row, `.row` with phone+email, message row, `.submit` with consent `<small>` + button) or a `<div class="form-success">` panel. Hidden honeypot inside `.honeypot`. Conditional `<div class="cf-turnstile">` inside `wire:ignore` rendered only when `IntegrationSettings::turnstile_site_key` is set
- `app/Filament/Resources/ContactRequestResource.php` — group **Заявки**, sort 10, icon `heroicon-o-inbox`, label **Обращения**. `canCreate() === false` (leads only come from the public form). Navigation badge = count of `status=new` rows (warning color). Table: created_at / name / phone / email / status (badge) / utm_source / utm_campaign / handled_at with status filter + searchable/copyable columns. Row actions: View, Edit, Mark handled (visible only when status ≠ handled; sets `status=Handled` + `handled_at=now()`). Bulk Mark handled. Infolist (View): *Контакт* + *Согласие на обработку ПД (152-ФЗ)* (hash shown as `prefix · актуально|устарело` vs current text) + *Трекинг* (UTM KeyValueEntry, referer, landing, IP, UA, timestamps — collapsed). Edit form: *Статус* (Select + DateTimePicker) + *Контакт* (read-only disabled fields) + *Метаданные* (collapsed Placeholders)
- `app/Filament/Resources/ContactRequestResource/Pages/{List,View,Edit}ContactRequest.php` — page classes; View has header actions Edit + Mark handled, Edit has View + Delete
- `resources/js/app.js` — `window.onTurnstileSuccess` + `window.onTurnstileExpired` handlers that dispatch `turnstile-verified` to Livewire so the widget (inside `wire:ignore`) can hand tokens to the component without breaking the Livewire DOM diff

### Changed
- `resources/views/partials/contact.blade.php` — 30-line static `<form>` replaced with `<livewire:contact-form />`; left-column `.info` rows + heading preserved verbatim

### Verified
- `php -l` on all 5 new PHP files: no syntax errors; Vite build 37.87 kB CSS + 39.17 kB JS in 191 ms
- `GET /` → `HTTP 200`, 50,753 B; view-source contains `<form wire:submit.prevent="submit" autocomplete="on" novalidate`, `wire:id`, `wire:model` × 6, `class="honeypot"`
- Action end-to-end (tinker): valid submit creates `ContactRequest` with `status=new`, `consent_text_hash` matching current sha256, parsed UTM, populated `ip_hash` + `user_agent` + `referer_url` + `landing_url`
- Mailable end-to-end: after setting `IntegrationSettings::notify_email = 'admin@crypton.local'`, submit → `storage/logs/laravel.log` contains *Subject: Новая заявка с сайта — {name}*, rendered HTML table of all fields, and *Открыть в админке* button linking to the admin edit page
- Honeypot: non-empty `website` → `DomainException` → Livewire flips to success state with **no row created**; row count unchanged (delta = 0)
- Rate limiter: 5 `RateLimiter::hit(...)` calls succeed; 6th `RateLimiter::tooManyAttempts(...)` returns `true` with 10-minute decay
- Turnstile paths: empty secret → always `true`; with secret + no token → `false`; with secret + fake token → `false` (Cloudflare endpoint rejects)
- `ContactRequestResource` metadata: `getModel()` = `ContactRequest`, group `Заявки`, sort `10`, label `Обращения`, icon `heroicon-o-inbox`, `canCreate()` = `false`, pages = `index, view, edit`, badge `1` (before flip)
- Mark-handled flow: `status: new → Handled`, `handled_at` populated, badge color `warning → success`, navigation badge decrements
- `php artisan route:list --path=admin/contact-requests` → 3 routes (`index`, `view`, `edit`)
- `grep -rn "env('TURNSTILE" app/ resources/` → **0 matches**
- `GET /admin/login` → `HTTP 200` (admin still healthy)

### Note
- Phase 2's `consent_text_hash` column (sha256, 64 chars) is used as-is; no new migration. The spec had mentioned `consent_hash` + sha1 — we stuck with the already-migrated schema (matches rosecology's audit pattern and the existing 64-char column width)

## [0.4.0] — 2026-04-23 — Phase 4: Filament admin

### Added
- `app/Providers/Filament/AdminPanelProvider.php` — customised: `brandName('Криптон · Админка')`, `Color::Orange` primary, IBM Plex Sans via `GoogleFontProvider`, 4 navigation groups in landing-page order (Контент, Справочники, Заявки, Настройки)
- `app/Models/User.php` now `implements FilamentUser` with `canAccessPanel()` returning `true` (single-tenant admin)
- `app/Filament/Concerns/BustsSettingsCache.php` — shared trait used by all 20 Settings pages; computes Spatie's actual cache key (`{prefix}.settings.{fqcn}`) and calls `Cache::store(...)->forget($key)` in `afterSave()`
- **20 Settings pages** in `app/Filament/Pages/`, one per Spatie Settings class, each with `$navigationGroup = 'Настройки'`, explicit `$navigationSort` in increments of 10 (10 → 200), Russian `$navigationLabel` / `$title`, `RichEditor` for `*_html` properties with `['bold','italic','link','undo','redo']` toolbar, `Select` for `DesignSettings` enums, `->password()->revealable()` for `turnstile_secret_key`
- **15 Resources** in `app/Filament/Resources/`, all in group **Справочники**, sort 10-150: NavItem, TickerItem, AboutLedgerRow, MetricTile, Service (Repeater for spec_rows), BitrixFeature, BitrixMockColumn (+ CardsRelationManager), Industry, WasteType (SpatieMediaLibraryFileUpload with imageEditor), ProcessStep, Plan (Repeater for features with hydrate/dehydrate), Region, MapPin (Select restricted to c1-c5), FooterColumn (+ LinksRelationManager), SiteAsset (upload-only, `canCreate=false`, `canDelete=false`)
- Each Resource has `ListXxx`, `CreateXxx`, `EditXxx` page classes under `Resources/XxxResource/Pages/` (SiteAsset has List+Edit only); reorder-by-`sort`, `is_active` filter, and Russian labels throughout
- 2 Relation managers: `BitrixMockColumnResource\RelationManagers\CardsRelationManager` (manages the kanban cards), `FooterColumnResource\RelationManagers\LinksRelationManager` (manages per-column links)

### Verified
- `GET /admin` → `HTTP 302` (redirect to login); `GET /admin/login` → `HTTP 200 OK` with `lang="ru"` and "Криптон" brand
- `php artisan route:list --name=filament.admin.pages` counts 20 `manage-*` routes; resources discover 15 index routes
- `Filament::getPanel('admin')` resolves 21 pages (20 settings + Dashboard), 15 resources, 4 named navigation groups in the configured order
- All 20 ManageXxxSettings classes and all 15 Resource classes instantiate without errors (reflected via bootstrapped test script)
- Cache-bust round-trip: after reading `HeroSettings`, key `settings.settings.App\Settings\HeroSettings` is HIT; after invoking `ManageHeroSettings::afterSave()` via reflection, same key is `miss` — confirms the trait wires up correctly
- `grep` finds 42 cache-forget references across 22 files (trait + 20 pages + 1 bustCache helper on NavItemResource)
- `GET /` still returns `HTTP 200 OK` — Phase 3 public site unaffected

### Changed
- `AdminPanelProvider::panel()` — swapped `Color::Amber` default for `Color::Orange`, added brand/font/groups, dropped `FilamentInfoWidget` from the dashboard widget list

### Note
- `ContactRequestResource` (group **Заявки**, sort 10) intentionally deferred to Phase 5 where it lands alongside the Livewire contact form, action, mailable, and enum-badged status column

## [0.3.0] — 2026-04-22 — Phase 3: frontend port

### Added
- `resources/css/app.css` — full design CSS ported verbatim from `index-v2.html` (lines 20-512), minus the dev-only `.tweaks` panel; small form-feedback block (honeypot, field-err, is-invalid, form-success) appended for the upcoming Livewire form
- `resources/js/app.js` — bootstrap import + smooth-scroll for anchor links (ported from `index-v2.html` lines 1232-1238)
- `app/Http/Controllers/HomeController.php` — invokable controller returning `view('pages.home')`
- `routes/web.php` — `Route::get('/', HomeController::class)`
- `resources/views/layouts/app.blade.php` — root HTML with `lang="ru"`, IBM Plex fonts, full SEO head (description, Open Graph, Twitter Card), conditional favicon + og:image from `SiteAsset`, conditional Cloudflare Turnstile script (reads from `IntegrationSettings::turnstile_site_key`, never `env()`), `<body>` data attrs from `DesignSettings`, Vite + Livewire directives
- `resources/views/pages/home.blade.php` — extends layout + includes 17 partials in design order
- `resources/views/components/section-head.blade.php` — reusable `.sec-head` (idx/kicker/h2/note), consumed by about/services/industries/waste/process/plans
- `resources/views/components/waste-fig.blade.php` — `<picture>` from media library when present, else fallback `.gly` glyph letter
- 17 partials in `resources/views/partials/`: strip, nav, hero, ticker, about, metrics, services, bitrix, industries, waste, process, plans, coverage, quote, cta-band, contact, footer — each consumes its matching Settings class + model via `app(...)` / `::ordered()->active()->get()`, echoes `_html` fields with `{!! !!}` (trusted) and plain strings with `{{ }}`
- Ticker's `TickerItem` collection rendered **twice** inside `.track` to satisfy the marquee keyframe (0 → -50%)
- Footer massive wordmark dynamically wraps the configured italic char (`FooterSettings::massive_italic_char`) in `<em>` via `mb_strpos`
- Hero card kicker preserves the `<b>§ 001</b>` bold prefix via `preg_replace` at render time

### Verified
- `npm run build` → manifest + 37.9 KB CSS + 38.8 KB JS in ~175 ms, no errors
- `php artisan serve` → `GET /` returns `HTTP 200 OK` with 47,619 bytes of valid HTML
- `<html lang="ru">`, `<body data-signal="hazard" data-paper="bone" data-head_weight="serif">` all present
- View-source: full `<title>`, meta description, 4 OG + 3 Twitter Card tags, IBM Plex font preconnect, Vite-generated CSS/JS, Livewire styles + scripts
- Favicon and og:image `<link>` / `<meta>` are absent (conditional) when no `SiteAsset` media is uploaded — correct
- Turnstile `<script>` is absent when `turnstile_site_key` is null — correct
- `DesignSettings::paper = 'noir'` via tinker + `Cache::flush()` → next request renders `data-paper="noir"` (whole site flips dark); revert works
- Element counts per section match seeds: 7 nav items, 12 ticker spans × 2, 4 ledger, 4 metrics, 3 services, 4 bitrix features, 3 kanban columns + 7 cards, 6 industries, 8 waste types, 5 process steps, 3 plans, 10 regions, 5 map pins, 3 footer columns

### Changed
- `routes/web.php` — default `welcome` closure replaced with `HomeController`

## [0.2.0] — 2026-04-22 — Phase 2: data model

### Added
- 20 Spatie Settings classes in `app/Settings/` covering every section of the landing page
- `database/settings/2026_04_22_120000_seed_all_settings.php` — defaults for all 20 groups, copy verbatim from `index-v2.html`
- 18 Eloquent models + 18 migrations: `NavItem`, `TickerItem`, `AboutLedgerRow`, `MetricTile`, `Service`, `BitrixFeature`, `BitrixMockColumn`, `BitrixMockCard`, `Industry`, `WasteType`, `ProcessStep`, `Plan`, `Region`, `MapPin`, `FooterColumn`, `FooterLink`, `SiteAsset`, `ContactRequest`
- `HasSortOrder` trait — `ordered()` + `active()` scopes on all content models
- `ContactRequestStatus` enum — 2 cases (New, Handled) with Russian labels + Filament color contracts
- `registerMediaConversions()` on `SiteAsset` (5 keys with per-key sizing) and `WasteType` (640/320 webp)
- 14 seeders: `DatabaseSeeder` orchestrates `AdminUserSeeder` + 12 content seeders + `SiteAssetSeeder`
- Admin user seeded from `ADMIN_EMAIL` / `ADMIN_PASSWORD` / `ADMIN_NAME` env

### Verified
- `migrate:fresh --seed` — all 20 migrations applied, all 14 seeders successful
- Row counts match design: 7 nav items, 6 ticker items, 4 ledger rows, 4 metrics, 3 services (1 featured), 4 bitrix features + 3 mock cols + 7 cards, 6 industries, 8 waste types (5 hazard), 5 process steps, 3 plans (1 highlighted), 10 regions, 5 map pins, 3 footer cols + 15 links, 5 site assets
- All 20 settings classes resolve via `app(...)` and return seeded Russian text
- JSON casts working (`Plan::features`, `Service::spec_rows`, `ContactRequest::utm`)
- Server still healthy at `/` + `/admin/login`

## [0.1.0] — 2026-04-22 — Phase 1: scaffold

### Added
- Laravel `11.51.0` installed with SQLite + file cache + sync queue + log mailer
- Runtime packages: `filament/filament ^3.2`, `spatie/laravel-medialibrary ^11`, `spatie/laravel-settings ^3`, `spatie/laravel-sluggable ^3`, `livewire/livewire ^3`, `laravel-lang/common ^6`, plus the two Filament Spatie plugins
- Russian translations for framework (`lang/ru/`) + all 7 Filament packages (`lang/vendor/filament*/ru/`)
- Storage symlink (`public/storage`)
- `config/settings.php` with cache profile enabled (file store, forever TTL, prefix `settings`)
- `.env` / `.env.example` configured for Russian, Europe/Moscow, SQLite, MAIL_MAILER=log, settings cache, admin env vars

### Removed
- Tailwind CSS and its npm deps (per core decision — handwritten CSS only)
- `tailwind.config.js`, `postcss.config.js`

### Verified
- `GET /` and `GET /admin/login` both return `200 OK`
- `php artisan about` — locale=ru, timezone=Europe/Moscow, drivers all correct

## [0.0.0] — 2026-04-22 — Project planning

### Added
- Design handoff bundle extracted from `claude.ai/design` (1269-line `index-v2.html`)
- Master implementation plan at `C:\Users\user\.claude\plans\fetch-this-design-file-sunny-sunset.md`
- 7-phase documentation in `docs/`: README, phase-01 through phase-07
- Project meta docs: `changelog.md`, `tech.md`, `core_decisions.md`

### Decided
- Laravel 11 + Filament 3 stack (mirrors `C:\Projects\rosecology_new\app`)
- Leads stored in DB + emailed to admin (no FastAPI forwarding)
- Every text fragment and key images editable from admin
- Russian only, seed with design's exact copy
- Anti-spam: honeypot + rate limit + Cloudflare Turnstile
