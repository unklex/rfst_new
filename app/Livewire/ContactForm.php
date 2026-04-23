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
    public string $name = '';

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
    public string $phone = '';

    #[Validate(
        rule: ['required', 'string', 'max:120', 'email:rfc'],
        message: [
            'email.required' => 'Укажите e-mail.',
            'email.email' => 'Укажите корректный e-mail.',
            'email.max' => 'E-mail слишком длинный.',
        ],
    )]
    public string $email = '';

    #[Validate(
        rule: ['required', 'string', 'min:10', 'max:2000'],
        message: [
            'message.required' => 'Коротко опишите задачу — это поможет нам подготовить ответ.',
            'message.min' => 'Сообщение слишком короткое (минимум 10 символов).',
            'message.max' => 'Сообщение слишком длинное (максимум 2000 символов).',
        ],
    )]
    public string $message = '';

    #[Validate(
        rule: ['accepted'],
        message: ['consent.accepted' => 'Нужно согласиться с обработкой персональных данных.'],
    )]
    public bool $consent = true;

    public string $turnstileToken = '';

    /** Honeypot — must stay empty. */
    public string $website = '';

    public string $landingUrl = '';

    public bool $submitted = false;
    public ?string $error = null;

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
    public function setTurnstileToken(string $token): void
    {
        $this->turnstileToken = $token;
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
        if ($this->website !== '') {
            $this->submitted = true;

            return;
        }

        RateLimiter::hit($ipKey, 600); // 5 / 10 min window

        $this->validate();

        try {
            ($action)([
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'message' => $this->message,
                'consent_accepted' => $this->consent,
                'turnstile_token' => $this->turnstileToken,
                'website' => $this->website,
                'landing_url' => $this->landingUrl,
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
