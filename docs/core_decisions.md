# Core Decisions

Architectural and scoping decisions, each with the reasoning. Append new decisions as `## YYYY-MM-DD — Short title`. Never rewrite history — if a decision is reversed, write a new entry that supersedes it.

---

## 2026-04-22 — Filament 3 as admin panel

**Decision**: use Filament 3.2 for the admin panel.

**Alternatives considered**: Laravel Nova (paid), Backpack, custom Blade CRUD.

**Why Filament**:
- Same stack as `rosecology_new` — port `ContactRequestResource` almost verbatim
- Spatie Settings and Media Library plugins give us upload fields + settings pages with no scaffolding
- Free, active community, mature at version 3.2

**Trade-off**: Filament has opinions about form/table structure; custom workflows require its form builder DSL. Accepted — our needs fit the defaults.

---

## 2026-04-22 — Store leads in DB + email admin (no external CRM push)

**Decision**: on form submission, write a `contact_requests` row and queue a Mailable to the admin inbox. No FastAPI forwarding, no direct Bitrix24 push.

**Alternatives considered**: port rosecology's `ForwardLeadToFastApiJob` as-is; push directly to Bitrix24 REST via webhook.

**Why DB + email**:
- Self-contained, no external dependencies at ship time
- Editors triage leads in Filament — same UX as rosecology
- Bitrix24 push can be added later (crypton sells Bitrix24; dogfooding makes sense) without re-architecting the action

**Trade-off**: loses the queue-retry resilience of the FastAPI job. Accepted — mail failures are rare and logged.

> **Superseded by 2026-04-23 — FastAPI forwarder restored, supersedes.** Kept in history for the reasoning.

---

## 2026-04-23 — FastAPI forwarder restored + Sentry added

**Decision**: port rosecology's `ForwardLeadToFastApiJob` to crypton (dispatched via `dispatch()->afterResponse()` so `QUEUE_CONNECTION=sync` still feels instant) and install `sentry/sentry-laravel`. Supersedes the 2026-04-22 "no external CRM push" decision.

**Alternatives considered**: keep the DB+email-only loop; add a Bitrix24 REST push instead of generic FastAPI.

**Why FastAPI forwarder now**:
- Client wants leads mirrored to their existing receiver (shared with rosecology's stack); rebuilding per-tenant would be wasted work
- The rosecology implementation is production-proven — porting is low-risk
- UTM, consent hash, landing URL forwarding are already baked in — matches the analytics ask
- `dispatch()->afterResponse()` means the POST fires *after* the HTTP response is flushed, so the user still sees the success panel instantly even with `QUEUE_CONNECTION=sync` (no worker or cron required on Beget shared hosting)
- Exception-safe inside the job (`handle()` never rethrows): network fails flip status to `failed`, non-2xx from upstream flips to `failed`, 2xx flips to `forwarded`. Admin sees it in the Filament badge and the "Отправить в FastAPI" action resends manually

**Why Sentry now**:
- Production shared-hosting debugging needs a second channel beyond `storage/logs/laravel.log` (which Beget's SSH exposes but admins don't monitor)
- DSN read at runtime from `IntegrationSettings::sentry_dsn`, with `env('SENTRY_LARAVEL_DSN')` as fallback — follows the same "runtime-editable, not env()-frozen" pattern as Turnstile
- sentry-laravel ^4 has auto-discovery and a minimal config.php — zero per-page wiring
- Wired in `bootstrap/app.php` via `\Sentry\Laravel\Integration::handles($exceptions)` so every unhandled exception plus the `report()` calls inside the forwarder job surface in Sentry automatically

**Trade-off**: adds 4 new columns (`fastapi_status_code`, `fastapi_response`, `forwarded_at`, `external_id`) to `contact_requests`, 2 new enum cases (`Forwarded`, `Failed`), 3 new settings (`fastapi_lead_url`, `fastapi_auth_token`, `sentry_dsn`), and one Jobs class — small scope. Sentry adds a dependency (~25 transitive packages) but it's a mature SDK.

**Implementation**:
- Migration `2026_04_23_120000_add_fastapi_columns_to_contact_requests.php` + settings migration `2026_04_23_120100_add_fastapi_and_sentry_to_integrations.php`
- `app/Jobs/ForwardLeadToFastApiJob.php` (port of rosecology, `service_id` removed, `source` derived from `APP_URL` host)
- `SubmitContactRequestAction` adds `dispatch(new ForwardLeadToFastApiJob($lead->id))->afterResponse()` **after** the Mail dispatch, guarded by `fastapi_lead_url` being non-empty
- `ContactRequestResource` — new table columns (fastapi_status_code badge, external_id, forwarded_at), infolist section, and "Отправить в FastAPI" row action visible only for `new`/`failed` leads
- `ManageIntegrationSettings` — adds *FastAPI — приёмник заявок* + *Sentry* sections
- `AppServiceProvider::register()` — runtime override of `config('sentry.dsn')` from IntegrationSettings with a try/catch around the DB read for pre-migrate safety

---

## 2026-04-22 — Every text fragment editable, not just collections

**Decision**: use 20 Spatie Settings classes to make **every** piece of page copy editable, not just the repeated items (services, industries, etc.).

**Alternatives considered**: only make collections editable; leave hero/strip/CTA/footer copy hardcoded in Blade.

**Why everything**:
- User's explicit ask ("maximum change from admin panel")
- Low additional cost — settings classes are cheap, Filament pages scaffold fast
- Unblocks non-technical content updates (hero headline, consent text, license number, etc.)

**Trade-off**: 20 settings pages in admin + 20 classes to maintain. Accepted. Mitigated by cache (see next decision).

---

## 2026-04-22 — Settings cache mandatory

**Decision**: enable the cache profile in `config/settings.php` with forever TTL, invalidated via `afterSave()` on each Filament Settings page.

**Why**: 20 settings injections per homepage render = 20 SELECTs on cold cache. Unacceptable for a marketing landing page.

**Implementation**:
- `'cache' => ['enabled' => true, 'store' => 'file', 'ttl' => null]` in `config/settings.php`
- Every `ManageXxxSettings` page overrides `afterSave()` with `Cache::forget('settings.{group}')`
- `DatabaseSeeder` ends with `Cache::forget('settings.*')`
- Custom `settings:clear-cache` Artisan command for deploy scripts

**Trade-off**: an editor who manipulates settings via tinker/DB directly must clear cache manually. Accepted — non-admin edits are discouraged.

---

## 2026-04-22 — Handwritten CSS, no Tailwind

**Decision**: paste the design's `<style>` block verbatim into `resources/css/app.css`. No Tailwind, no utility-first conversion.

**Alternatives considered**: convert to Tailwind utilities (stock Laravel 11 scaffolds it); extract to component library.

**Why verbatim**:
- The design is editorial (IBM Plex + paper/ink palette + custom grid) — Tailwind utilities would fight it, not help
- Preserves pixel-perfect match with prototype
- Every CSS variable (`--paper`, `--signal`, `--ink-N`) already supports the "tweaks" switches via `[data-signal="..."]` selectors — moving to Tailwind would require re-architecting this with arbitrary values or CSS-in-JS
- Single ~500-line CSS file is smaller than a compiled Tailwind bundle for this scope

**Trade-off**: harder to reuse styles across future pages. Accepted — this is a single-page site; if a second page appears later, we refactor then.

---

## 2026-04-22 — `$navigationSort` everywhere in Filament

**Decision**: every Settings page and every Resource declares `protected static ?int $navigationSort = X;`. The admin menu mirrors the **top-to-bottom flow of the landing page**.

**Why**: Filament's default alphabetical fallback produces a menu where "Battery types" appears before "Hero" — useless for an editor who thinks in page order.

**Implementation**: allocated 10-unit gaps (10, 20, 30, ...) per the table in `docs/phase-04-filament-admin.md` so future inserts don't require re-numbering.

---

## 2026-04-22 — Turnstile keys from `IntegrationSettings`, never `env()`

**Decision**: read Turnstile site key and secret from `IntegrationSettings` at runtime in Blade + action code. `.env` has no `TURNSTILE_*` entries.

**Why**: `php artisan config:cache` on production freezes `env()` values into the compiled cache at deploy time. If an admin later adds keys via the `.env`, Laravel wouldn't pick them up without a cache flush — and they'd still be invisible to the admin UI. Storing in the DB via `IntegrationSettings` means keys are runtime-editable, encrypted at rest (`turnstile_secret_key` cast as `encrypted`), and survive `config:cache`.

**Verification**: `grep -r "env('TURNSTILE" app/ resources/` must return zero matches.

---

## 2026-04-22 — Russian only, with `рус/en` toggle as static link

**Decision**: single-locale Russian build. The strip's `рус / en` toggle is a static link (no target).

**Why**: the design is Russian-only; adding i18n doubles every Settings class + doubles seed data + adds route/session complexity. If English is needed later, `laravel-lang/common` + Spatie's `translatable` on Settings would be the path.

**Trade-off**: "en" link is a dead link. Acceptable — visible only to bots for now.

---

## 2026-04-22 — Seed DB with exact design copy

**Decision**: `database/migrations/2026_04_22_120000_seed_all_settings.php` seeds every setting with the literal Russian text from `index-v2.html`. 12 content seeders populate repeated items.

**Why**: after `migrate --seed`, the homepage renders identically to the prototype. Editors see real content in admin, not placeholder Lorem Ipsum.

**Trade-off**: seed data goes stale if the design changes post-launch. Accepted — seeds are dev onboarding only; production admins edit via UI.

---

## 2026-04-22 — Media conversions mandatory, not opt-in

**Decision**: `SiteAsset` and `WasteType` implement `registerMediaConversions()` with `->nonQueued()` webp variants at role-specific widths.

**Why**: a single-page marketing site shouldn't serve unoptimized JPEGs. `nonQueued()` avoids needing a queue worker for media uploads during admin edits.

**Implementation**: per-key sizing (hero/og=1920, about=1280, quote=480, favicon=180, waste=640).

**Trade-off**: eager conversions slow down the admin save action when uploading large originals. Accepted — editors upload a few times, visitors load every pageview.

---

## 2026-04-22 — No page builder / no block system

**Decision**: fixed section order in `pages/home.blade.php`, no dynamic ordering, no drag-and-drop page assembly.

**Alternatives considered**: Filament Builder field with sections as blocks.

**Why fixed**:
- Editorial design — section order is part of the design, not configurable
- Builder UX adds implementation cost and editor confusion
- "Can we move the pricing section above industries?" is rare and justifies a code change

**Trade-off**: no easy A/B reorganization. Accepted — this is a brochure site, not a CMS.

---

## 2026-04-22 — Seven-phase execution, not one-shot

**Decision**: work in 7 discrete phases with verification between each, documented in `docs/phase-0N-*.md` and closed out with `complete_phase_N.md`.

**Why**: 80–100 files is too many to ship without verification checkpoints. Mid-course corrections cost less than end-of-build rewrites. Each phase has a natural verification target (page renders / admin works / form submits / etc.).

**Trade-off**: more ceremony. Accepted — the alternative is discovering Phase 3 depends on a wrong Phase 2 assumption after 50 files are written.
