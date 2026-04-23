# Phase 5 — Complete

**Status**: ✅ Done — 2026-04-23
**Time**: ~35 min

## What landed

### Actions (`app/Actions/Contact/`)

- **`VerifyTurnstileAction`** — Cloudflare Turnstile server-side verification. Reads `turnstile_secret_key` from `IntegrationSettings` at runtime (never `env()`). Returns `true` when the secret is empty (dev/off mode), returns `false` on network errors, non-2xx responses, or `success=false`. Logs network errors via `Log::warning`, captcha failures via `Log::info`.
- **`SubmitContactRequestAction`** — invokable action. Pipeline:
  1. Honeypot check → throws `\DomainException('Honeypot triggered')` on non-empty `website`
  2. Consent check → throws with Russian copy if `consent_accepted !== true`
  3. Turnstile verify → throws "проверка антибота не пройдена" if captcha failed
  4. Insert `ContactRequest` row (sha256 of current `ContactSettings::personal_data_consent_text` stored in `consent_text_hash`; UTM parsed from `landing_url` query string; `ip_hash` = sha256 of client IP; `user_agent` truncated to 512 chars; `status = ContactRequestStatus::New`)
  5. If `IntegrationSettings::notify_email` is non-null → `Mail::to($email)->queue(new NewContactRequestMail($lead))`; if null → skip silently
  Injects `VerifyTurnstileAction`, `ContactSettings`, `IntegrationSettings`.

### Mailable + Markdown view

- **`app/Mail/NewContactRequestMail.php`** — `implements ShouldQueue`, `use Queueable`. Subject: `"Новая заявка с сайта — {name}"`. Uses `markdown: 'mail.new-contact-request'`.
- **`resources/views/mail/new-contact-request.blade.php`** — `mail::message` wrapping a 10-row table (name, phone, e-mail, message, UTM JSON, referer, landing URL, IP hash, User-Agent, created timestamp) + a `mail::button` linking to `/admin/contact-requests/{id}`.

With `.env` already having `MAIL_MAILER=log` and `QUEUE_CONNECTION=sync` from Phase 1, the mail renders to `storage/logs/laravel.log` on submit without needing a queue worker.

### Livewire component + view

- **`app/Livewire/ContactForm.php`**:
  - Properties: `$name`, `$phone`, `$email`, `$message`, `$consent`, `$turnstileToken`, `$website` (honeypot), `$landingUrl`, `$submitted`, `$error`
  - `#[Validate]` attributes with Russian messages. `name`: `required|string|min:2|max:120`. `phone`: `required|string|max:32|regex:/^\+?[78]?[\s\-]?\(?\d{3}\)?[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}$/`. `email`: `required|string|max:120|email:rfc`. `message`: `required|string|min:10|max:2000`. `consent`: `accepted`.
  - `mount()` captures full URL to `$landingUrl` (so UTM can be extracted even though the Livewire submit POST lacks them).
  - `#[Computed] settings()` → `ContactSettings`; `#[Computed] turnstileKey()` → `IntegrationSettings::turnstile_site_key` or null
  - `#[On('turnstile-verified')]` listener sets `$turnstileToken`
  - `submit()` applies rate limit (`contact-form:{ip}`, 5 per 10 minutes = 600 s decay), short-circuits honeypot to success state, validates, calls the action, swaps to `$submitted = true` on success
- **`resources/views/livewire/contact-form.blade.php`** — root `<div>` containing either the `<form wire:submit.prevent="submit">` (pixel-matching the design: single `<div>` wrapper for name, `.row` for phone+email, single `<div>` for message, `.submit` row with consent `<small>` on the left and `<button class="btn btn-signal">` on the right) or the `<div class="form-success">` success panel. Labels/placeholders/submit text come from `ContactSettings`. Per-field `<span class="field-err">` + `.is-invalid` class on the input via `@class(...)`. Hidden honeypot `<input name="website" wire:model="website">` inside `.honeypot`. Conditional `<div class="cf-turnstile" data-sitekey="{{ $turnstileKey }}" wire:ignore>` renders only when the site key is set.

### Static-form swap

`resources/views/partials/contact.blade.php` — the 30-line static `<form onsubmit="alert(...)">` block replaced with `<livewire:contact-form />`. The left-column `.info` rows and heading preserved verbatim so the design match stays pixel-perfect.

### Turnstile callback bridge

`resources/js/app.js` — added top-level `window.onTurnstileSuccess` and `window.onTurnstileExpired` handlers that `Livewire.dispatch('turnstile-verified', {...})` so the widget (inside `wire:ignore`) can hand the token to the component. The Turnstile `<script>` include is already conditional in `layouts/app.blade.php` (renders only when `IntegrationSettings::turnstile_site_key` is set — Phase 3 landed that).

### Filament `ContactRequestResource` (group Заявки, sort 10)

- **`app/Filament/Resources/ContactRequestResource.php`** — navigation: icon `heroicon-o-inbox`, label **Обращения**, modelLabel **Заявка**, pluralModelLabel **Заявки**, sort 10. `canCreate()` returns `false` (leads come from the public form only). Badge = count of rows with `status = new` (color `warning`), hidden when 0.
- **Table**: sortable `created_at`, `name` (searchable), `phone` (searchable + copyable), `email` (toggleable + searchable), status **badge** (colors from `ContactRequestStatus::getColor()` — warning for new, success for handled), `utm_source` + `utm_campaign` (toggleable, extracted from JSON column), `handled_at` (toggleable). Default sort `created_at desc`. Status filter using all enum cases.
- **Row actions**: View, Edit, **Mark handled** (visible only when `status !== Handled`, sets `status = Handled` + `handled_at = now()`). Bulk **Mark handled** action too.
- **Form (Edit page)** — three sections: *Статус* (status Select + handled_at DateTimePicker), *Контакт* (read-only `disabled()->dehydrated(false)` name/phone/email/message fields), *Метаданные* (collapsed by default, Placeholder entries for created_at, ip_hash, user_agent, referer, landing, consent hash with actuality indicator).
- **Infolist (View page)** — *Контакт* + *Согласие на обработку ПД (152-ФЗ)* (hash shown as `prefix · актуально|устарело` based on comparison to current `ContactSettings::personal_data_consent_text` hash) + *Трекинг* (UTM KeyValueEntry, referer, landing, IP hash, UA, timestamps), collapsed.
- **Pages**: `ListContactRequests`, `ViewContactRequest` (header actions: Edit + Mark handled), `EditContactRequest` (header actions: View + Delete).

### No new migration / no .env edit

- The Phase 2 `create_contact_requests_table` migration already defined `consent_text_hash varchar(64)` — matches the sha256 hash we write. **Deviation from spec**: the spec said to hash with `sha1` and store in `consent_hash`; the existing Phase 2 schema is sha256 + `consent_text_hash` (matches rosecology's audit pattern). Kept the migrated schema; adapted the action accordingly.
- `.env` already has `MAIL_MAILER=log`, `QUEUE_CONNECTION=sync`, `MAIL_TO_ADMIN=admin@crypton.local` from Phase 1. No edits needed.

## Verification

Tinker-driven end-to-end sweep with `php artisan serve` running.

| Check | Result |
|---|---|
| `php -l` on all 5 new PHP files | ✅ no syntax errors |
| `npx vite build` | ✅ CSS 37.87 kB + JS 39.17 kB, 191 ms |
| `GET /` | ✅ `HTTP 200`, 50,753 bytes (was 47,619 pre-Livewire; +3,134 B for the Livewire directives/root div/hidden honeypot + widget scaffolding) |
| View-source contains `<form wire:submit.prevent="submit" autocomplete="on" novalidate` | ✅ |
| View-source contains `wire:id="..."` + `class="honeypot"` + `wire:model` × 6 | ✅ |
| Submit valid form (via action, tinker): `name + phone + email + message + consent=true + empty website + empty turnstile + landing URL with utm_source & utm_campaign` | ✅ Row created. `status=new`, `consent_text_hash` matches sha256 of current text, `utm={"utm_source":"test","utm_campaign":"phase5"}`, `ip_hash` prefix populated, `referer_url` captured, `landing_url` captured |
| After setting `IntegrationSettings::notify_email = 'admin@crypton.local'` + resubmit | ✅ Log contains **"Subject: Новая заявка с сайта — Мария Почтовая"**, rendered Markdown table, and **"Открыть в админке" → http://127.0.0.1:8000/admin/contact-requests/1** button |
| Honeypot test: `website = 'http://spam.example.com'` → expect silent drop | ✅ `DomainException('Honeypot triggered')` at action; Livewire's `submit()` catches it and flips to success state; **row count unchanged** (delta = 0) |
| Rate limiter: 6 rapid hits on `contact-form:{ip}` | ✅ attempts 1-5 `blocked=false`, attempt 6 `blocked=true`, 10-minute decay |
| Turnstile OFF (no secret, no token) | ✅ returns `true` |
| Turnstile OFF (no secret, stray token) | ✅ returns `true` |
| Turnstile ON (`secret='bogus-secret-for-test'`, no token) | ✅ returns `false` |
| Turnstile ON (bogus secret + fake token → Cloudflare rejects) | ✅ returns `false` |
| `ContactRequestResource::getModel/Group/Sort/Label/Icon/Badge` | ✅ `ContactRequest` / `Заявки` / `10` / `Обращения` / `heroicon-o-inbox` / `1` (before flip) |
| `ContactRequestResource::canCreate()` | ✅ `false` — no Create page registered |
| `php artisan route:list --path=admin/contact-requests` | ✅ 3 routes: `index` + `view` + `edit` |
| Mark-handled flow: `status=new → Handled; handled_at = now()` | ✅ badge color flips `warning → success`; navigation badge decrements from 1 to 0 (hidden) |
| `GET /admin/login` | ✅ `HTTP 200` (admin still healthy) |
| `grep -rn "env('TURNSTILE" app/ resources/` | ✅ **0 matches** — Turnstile keys never touched via env |
| `RateLimiter::clear(...)` + `ContactRequest::truncate()` | ✅ Dev DB + limiter state reset to clean |

## Deviations from plan

1. **`consent_text_hash` + sha256 instead of `consent_hash` + sha1**. The Phase 2 migration already defined a 64-char `consent_text_hash` column (sha256-compatible) — matches rosecology's audit trail pattern. The spec text said sha1; we kept the migrated sha256 and referenced `ContactSettings::personal_data_consent_text` directly. No new migration needed.
2. **Honeypot returns success state without counting against rate limit**. Rosecology's action throws `DomainException` *after* the Livewire caller has already `RateLimiter::hit()`-ed. We moved the honeypot check **before** the rate-limit hit in the Livewire component (the action still also checks, belt-and-braces). Reasoning: rate-limiting bots makes it harder for legitimate users on the same NAT; bots are silent-dropped anyway so the limiter doesn't need to see them.
3. **No Service dropdown / no `service_id`** — crypton's `ContactRequest` schema never had `service_id` (removed from the rosecology port in Phase 2). The Livewire component accordingly has no `services()` computed, no `service_id` property, no `<select>`.
4. **Validation strengthened per spec** — `email` now `required`, `message` now `required|min:10` (vs rosecology's `nullable`). Other fields' rules match rosecology.
5. **Consent checkbox inline with submit row `<small>`**. The design's `.submit` row has a single `<small>` on the left. To avoid restructuring the grid, the `<label>` + `<input type="checkbox">` was nested inside the `<small>` with inline styles that match the mono-uppercase type spec of the original. The separate `form_consent_text` (for the disclaimer) and `personal_data_consent_text` (the longer 152-ФЗ text hashed on submit) remain as two distinct `ContactSettings` fields.
6. **`ContactRequestResource` Edit page included**. The spec said "List + View + Edit for status flips" — all three are registered. Create is explicitly disabled (`canCreate() === false`). Form fields for the contact payload are `disabled()->dehydrated(false)` so editors can't mutate submitted user data; only the status section is editable.
7. **`ContactRequestResource::getNavigationLabel() = 'Обращения'`** (spec said *"Заявки → Обращения"*); the group itself is "Заявки" so the label "Обращения" disambiguates. Plural model label is still "Заявки" so table headers read naturally.
8. **Turnstile `<script>` include lives in the layout, not the Livewire view** — already wired up in Phase 3 (`layouts/app.blade.php:45`). The Livewire view only ever renders the `<div class="cf-turnstile">` widget; the `<script>` is conditionally loaded at `<head>` level so the widget has its API ready on first paint.
9. **Dev-mode mail arrival**: with `QUEUE_CONNECTION=sync`, `Mail::queue(...)` dispatches immediately; the log driver renders the Markdown mail template to HTML in `storage/logs/laravel.log`. No queue worker needed; no log flush step needed.

## Files created in Phase 5

```
app/Actions/Contact/
  VerifyTurnstileAction.php
  SubmitContactRequestAction.php

app/Mail/
  NewContactRequestMail.php

app/Livewire/
  ContactForm.php

app/Filament/Resources/
  ContactRequestResource.php
  ContactRequestResource/
    Pages/
      ListContactRequests.php
      ViewContactRequest.php
      EditContactRequest.php

resources/views/
  livewire/contact-form.blade.php
  mail/new-contact-request.blade.php
```

Modified:
- `resources/views/partials/contact.blade.php` — 30-line static `<form>` replaced with `<livewire:contact-form />`
- `resources/js/app.js` — added `window.onTurnstileSuccess` + `window.onTurnstileExpired` handlers that dispatch `turnstile-verified` to Livewire

Total: **10 new files + 2 modified**.

## Manual UI sanity (recommended before ship)

Tinker exercises the server-side flow; the Livewire frontend still wants a once-over in a real browser:

1. Submit empty form → four Russian validation errors render inline with `.field-err` styling
2. Submit valid form → grid cell transitions from `.contact form` to `<div class="form-success">` with "Заявка отправлена" heading + thank-you body, no page reload
3. Open `/admin/contact-requests` → row appears with status badge "Новая" in warning color; UTM columns populated (when visiting the form via `?utm_source=...`)
4. Click Mark Handled → badge flips to "Обработана" in success color, `handled_at` column populated, navigation badge count decreases
5. Paste Cloudflare test site key `1x00000000000000000000AA` + test secret `1x0000000000000000000000000000000AA` into `/admin/integrations` → widget appears below the message textarea, form submits cleanly; clear the secret → widget vanishes on next render

## Next — Phase 6

Favicon, Open Graph, Twitter Card, meta description — all from `SiteAsset` + `GeneralSettings`. Verify the `nonQueued()` webp conversion pipeline end-to-end: upload a hero JPG in admin, confirm webp variants appear in `storage/app/public/...`, view-source shows the correct URLs in `<link rel="icon">` + `<meta property="og:image">`.
