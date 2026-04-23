# Phase 5 — Contact Form

**Goal**: the contact section at the bottom of the homepage becomes a real Livewire form. Submissions are validated, rate-limited, honeypot-protected, Turnstile-verified, stored in `contact_requests`, and emailed to the admin. An editor can triage leads in Filament under **Заявки → Заявки с сайта**.

## Prerequisites

- Phase 4 complete: admin panel live, `IntegrationSettings` editable
- `partials/contact.blade.php` currently shows the static form from the prototype (will be replaced here)

## Reference (port these — do not re-invent)

- `C:\Projects\rosecology_new\app\app\Livewire\ContactForm.php` — the reactive component
- `C:\Projects\rosecology_new\app\app\Actions\Contact\SubmitContactRequestAction.php` — domain logic
- `C:\Projects\rosecology_new\app\app\Actions\Contact\VerifyTurnstileAction.php` — Turnstile verification with empty-secret bypass
- `C:\Projects\rosecology_new\app\app\Filament\Resources\ContactRequestResource.php` — admin CRUD
- `C:\Projects\rosecology_new\app\resources\views\livewire\contact-form.blade.php` — reference only; we rewrite markup to match crypton design

## Tasks

### 1. `VerifyTurnstileAction`

File: `app/Actions/Contact/VerifyTurnstileAction.php`

**Copy verbatim** from rosecology. It calls `https://challenges.cloudflare.com/turnstile/v0/siteverify`, returns `true` when the secret is empty (dev/off mode), returns `false` on network errors or failed verification. No edits needed.

Inject `IntegrationSettings` to read `turnstile_secret_key`.

### 2. `NewContactRequestMail`

File: `app/Mail/NewContactRequestMail.php`

Queued Markdown mailable.

```php
namespace App\Mail;

use App\Models\ContactRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};

class NewContactRequestMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactRequest $lead) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Новая заявка с сайта — {$this->lead->name}");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.new-contact-request', with: ['lead' => $this->lead]);
    }
}
```

View: `resources/views/mail/new-contact-request.blade.php`

```blade
@component('mail::message')
# Новая заявка

| Поле | Значение |
|---|---|
| **Имя** | {{ $lead->name }} |
| **Телефон** | {{ $lead->phone }} |
| **E-mail** | {{ $lead->email }} |
| **Сообщение** | {{ $lead->message }} |
| **UTM** | {{ json_encode($lead->utm, JSON_UNESCAPED_UNICODE) }} |
| **Referer** | {{ $lead->referer_url ?? '—' }} |
| **Landing** | {{ $lead->landing_url ?? '—' }} |
| **Создано** | {{ $lead->created_at->format('d.m.Y H:i') }} |

@component('mail::button', ['url' => url('/admin/contact-requests/' . $lead->id)])
Открыть в админке
@endcomponent

@endcomponent
```

Publish mail templates: `php artisan vendor:publish --tag=laravel-mail` (one-time, gives access to `resources/views/vendor/mail/`).

### 3. `SubmitContactRequestAction`

File: `app/Actions/Contact/SubmitContactRequestAction.php`

**Copy from rosecology**, then apply these surgical edits:

1. Remove `use App\Jobs\ForwardLeadToFastApiJob;`
2. In the constructor, inject `IntegrationSettings` alongside `SiteSettings` (rosecology already injects SiteSettings)
3. In `ContactRequest::create([...])`, **drop** the `service_id` key
4. **Replace rosecology line 70** (`ForwardLeadToFastApiJob::dispatch($lead->id)`) with:

```php
$to = $this->integrationSettings->notify_email ?? (string) env('MAIL_TO_ADMIN', 'admin@example.com');
Mail::to($to)->queue(new NewContactRequestMail($lead));
```

5. Keep everything else: honeypot silent-drop, consent requirement, Turnstile verification, UTM/IP/UA capture, rate limit.

### 4. `ContactForm` Livewire component

File: `app/Livewire/ContactForm.php`

**Port from rosecology** with these edits:

- Remove `public ?int $service_id = null;` property + its `#[Validate]` rule
- Remove the `services()` computed method
- Change `$email` validation from `nullable|email` to `required|email:rfc`
- Change `$message` validation to `required|string|min:10|max:2000`
- Keep: `$name`, `$phone` (with rosecology's Russian regex `/^\+?[78]?[\s\-]?\(?\d{3}\)?[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}$/`), `$consent`, `$turnstileToken`, `$website` (honeypot), `$landingUrl`
- Keep the `RateLimiter::tooManyAttempts('contact-form:'.$ip, 5)` guard, 60s decay
- Keep the success-state render (`$this->submitted = true`)

### 5. `ContactForm` view

File: `resources/views/livewire/contact-form.blade.php`

Rewrite markup to match the design `.contact form` pattern (prototype lines 1133-1156):

- Four text blocks with bottom-border serif inputs
- One grid row for phone + email side-by-side
- Message textarea
- `.submit` row with small consent text on the left, submit button on the right
- Honeypot: `<input type="text" name="website" wire:model="website" tabindex="-1" autocomplete="off" class="honeypot">` (CSS `position:absolute;left:-9999px`)
- Turnstile widget: `<div class="cf-turnstile" data-sitekey="{{ $turnstileKey }}" data-callback="onTurnstileSuccess"></div>` — only render when `$turnstileKey` is set

Turnstile key binding (top of the view):

```blade
@php($turnstileKey = app(\App\Settings\IntegrationSettings::class)->turnstile_site_key)
```

Success state (after submission): replace form with a single `<div class="form-success">` echoing "Заявка отправлена. Мы ответим в рабочее время."

### 6. Swap the static form

In `resources/views/partials/contact.blade.php`, replace the static `<form onsubmit="alert(...)">` block with:

```blade
<livewire:contact-form />
```

### 7. Turnstile callback script

Add to `resources/js/app.js`:

```js
window.onTurnstileSuccess = function (token) {
    Livewire.dispatch('turnstile-verified', { token });
};
```

In the Livewire component, listen:

```php
#[On('turnstile-verified')]
public function setTurnstileToken(string $token): void
{
    $this->turnstileToken = $token;
}
```

### 8. `ContactRequestResource` (Filament)

File: `app/Filament/Resources/ContactRequestResource.php`

**Port from rosecology** with these deletions:

- Remove `fastapi_status_code`, `fastapi_response`, `forwarded_at`, `external_id` columns/form fields
- Remove the "Resend to FastAPI" action (rosecology's "отправить повторно")
- Remove the "Forwarded" / "Failed" status filter options (enum only has `New` / `Handled` now)

**Keep**:

- Table columns: created_at, name, phone (copyable), email, status badge, handled_at
- Form sections: contact info, consent metadata, UTM/tracking (collapsed)
- "Mark as Handled" action (visible for `New` status only, sets `status = Handled`, `handled_at = now()`)
- Bulk "Mark as Handled" action
- Status filter
- Badge on the nav item showing count of `New` leads

Set `$navigationGroup = 'Заявки'`, `$navigationSort = 10`, `$navigationIcon = 'heroicon-o-inbox'`.

### 9. Dev mail + queue

Set in `.env`:

```
MAIL_MAILER=log
QUEUE_CONNECTION=sync
```

`QUEUE_CONNECTION=sync` ensures mail dispatches synchronously in dev so you can see it in the log immediately. In production, switch to `database` or `redis`.

## Verification

- [ ] `/` — contact section renders the Livewire form (view source: `<div wire:id="...">`)
- [ ] Submit empty form → four Russian validation errors appear inline
- [ ] Submit valid form:
  - Row appears in `contact_requests` with `status = 'new'` (`php artisan tinker` → `\App\Models\ContactRequest::latest()->first()`)
  - `storage/logs/laravel.log` contains the Markdown-rendered mail body to `admin@crypton.local`
  - UI flips to success state
- [ ] Filament sidebar shows **Заявки → Заявки с сайта** with badge `1`
- [ ] Open the request in Filament → all fields populated, UTM/tracking section collapsed
- [ ] Click "Mark as Handled" → status flips, badge decrements, `handled_at` populated
- [ ] **Honeypot test**: in browser devtools, set `website` input value to "bot", submit → response says "success" but **no row** created, **no mail** sent (silent drop)
- [ ] **Rate limit**: submit 6× rapidly → 6th submission shows "Слишком много попыток. Попробуйте через N сек."
- [ ] **Turnstile disabled**: empty `IntegrationSettings::turnstile_secret_key` → form submits without widget
- [ ] **Turnstile test keys**: set site_key `1x00000000000000000000AA` + secret `1x0000000000000000000000000000000AA` → widget loads, form submits
- [ ] **Turnstile bad secret**: set an invalid secret → form rejects with Russian error "проверка антибота не пройдена"
- [ ] `grep -r "env('TURNSTILE" app/ resources/` returns **zero** matches

## Common pitfalls

- **Turnstile widget doesn't render**: `$turnstileKey` is null. Check `IntegrationSettings::turnstile_site_key` in admin.
- **`config:cache` froze an empty Turnstile key**: you violated the explicit rule — keys must come from settings. Run `php artisan config:clear` and replace `env(...)` with `app(IntegrationSettings::class)->...`.
- **Honeypot rejects legitimate users**: browsers with autofill may fill the `website` field. Use `autocomplete="off"` and off-screen positioning; the CSS `.honeypot{position:absolute;left:-9999px;height:0;overflow:hidden}` is in rosecology's app.css — you should have appended it in Phase 3.
- **Rate limiter too aggressive**: 5/60s is for production. In dev set `'contact-form:'.gethostbyname(gethostname())` or temporarily raise the cap.
- **Mail not landing in log**: `MAIL_MAILER` unset or `QUEUE_CONNECTION=database` without a running queue worker. Use `sync` in dev.
- **Livewire component not found**: run `php artisan view:clear` and `php artisan livewire:discover`.

## Next

Phase 6 wires favicons, Open Graph, Twitter cards, and sitemap to settings + verifies the webp pipeline end-to-end.
