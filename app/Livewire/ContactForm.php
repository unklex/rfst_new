<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\Contact\SubmitContactRequestAction;
use App\Settings\ContactSettings;
use App\Settings\IntegrationSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ContactForm extends Component
{
    #[Validate(
        rule: ['required', 'string', 'min:2', 'max:120'],
        message: [
            'name.required' => 'Укажите имя или название компании.',
            'name.min' => 'Имя слишком короткое.',
            'name.max' => 'Имя слишком длинное.',
        ],
    )]
    /** @var string */
    public $name = '';

    #[Validate(
        rule: [
            'required',
            'string',
            'max:32',
            'regex:/^\+?[78]?[\s\-]?\(?\d{3}\)?[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}$/',
        ],
        message: [
            'phone.required' => 'Укажите телефон для связи.',
            'phone.regex' => 'Укажите телефон в формате +7 (XXX) XXX-XX-XX.',
            'phone.max' => 'Слишком длинный номер телефона.',
        ],
    )]
    /** @var string */
    public $phone = '';

    #[Validate(
        rule: ['required', 'string', 'max:120', 'email:rfc'],
        message: [
            'email.required' => 'Укажите e-mail.',
            'email.email' => 'Укажите корректный e-mail.',
            'email.max' => 'E-mail слишком длинный.',
        ],
    )]
    /** @var string */
    public $email = '';

    #[Validate(
        rule: ['required', 'string', 'min:10', 'max:2000'],
        message: [
            'message.required' => 'Коротко опишите задачу — это поможет нам подготовить ответ.',
            'message.min' => 'Сообщение слишком короткое (минимум 10 символов).',
            'message.max' => 'Сообщение слишком длинное (максимум 2000 символов).',
        ],
    )]
    /** @var string */
    public $message = '';

    #[Validate(
        rule: ['accepted'],
        message: ['consent.accepted' => 'Нужно согласиться с обработкой персональных данных.'],
    )]
    /** @var bool */
    public $consent = true;

    /** @var string */
    public $turnstileToken = '';

    /** Honeypot — must stay empty. */
    public $website = '';

    /** @var string */
    public $landingUrl = '';

    /** @var bool */
    public $submitted = false;

    /** @var string|null */
    public $error = null;

    public function mount(Request $request): void
    {
        $this->landingUrl = (string) $request->fullUrl();
    }

    #[Computed]
    public function settings(): ContactSettings
    {
        return app(ContactSettings::class);
    }

    #[Computed]
    public function turnstileKey(): ?string
    {
        $key = app(IntegrationSettings::class)->turnstile_site_key;

        return is_string($key) && $key !== '' ? $key : null;
    }

    #[On('turnstile-verified')]
    public function setTurnstileToken(mixed $token): void
    {
        $this->turnstileToken = is_string($token) ? $token : '';
    }

    public function submit(SubmitContactRequestAction $action, Request $request): void
    {
        $this->error = null;

        $ipKey = 'contact-form:' . (string) $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            $seconds = RateLimiter::availableIn($ipKey);
            $this->error = "Слишком много попыток. Попробуйте через {$seconds} сек.";

            return;
        }

        // Honeypot: silent success. Don't let bots learn why the submission was dropped.
        // Also don't count against rate limit — we haven't "hit" anything.
        if (! is_string($this->website) || $this->website !== '') {
            $this->submitted = true;

            return;
        }

        RateLimiter::hit($ipKey, 600); // 5 / 10 min window

        $validated = $this->validate();

        try {
            ($action)([
                'name' => (string) $validated['name'],
                'phone' => (string) $validated['phone'],
                'email' => (string) $validated['email'],
                'message' => (string) $validated['message'],
                'consent_accepted' => (bool) $validated['consent'],
                'turnstile_token' => is_string($this->turnstileToken) ? $this->turnstileToken : '',
                'website' => '',
                'landing_url' => is_string($this->landingUrl) ? $this->landingUrl : '',
            ], $request);
        } catch (\DomainException $e) {
            // Honeypot catch is a belt-and-braces — the action also checks.
            if (str_contains($e->getMessage(), 'Honeypot')) {
                $this->submitted = true;

                return;
            }
            $this->error = $e->getMessage();

            return;
        }

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
