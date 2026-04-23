<?php

declare(strict_types=1);

namespace App\Actions\Contact;

use App\Enums\ContactRequestStatus;
use App\Mail\NewContactRequestMail;
use App\Models\ContactRequest;
use App\Settings\ContactSettings;
use App\Settings\IntegrationSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class SubmitContactRequestAction
{
    public function __construct(
        private readonly VerifyTurnstileAction $verifyTurnstile,
        private readonly ContactSettings $contactSettings,
        private readonly IntegrationSettings $integrationSettings,
    ) {
    }

    /**
     * @param array{
     *   name: string,
     *   phone: string,
     *   email?: ?string,
     *   message?: ?string,
     *   consent_accepted: bool,
     *   turnstile_token?: ?string,
     *   website?: ?string,
     *   landing_url?: ?string,
     * } $data
     *
     * @throws \DomainException on honeypot / captcha / consent failures
     */
    public function __invoke(array $data, Request $request): ContactRequest
    {
        // Honeypot — silently drop bot submissions. Livewire caller swallows the
        // exception and shows the success state so bots can't learn the trigger.
        if (!empty($data['website'] ?? null)) {
            Log::info('Honeypot triggered on contact form', [
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
            ]);

            throw new \DomainException('Honeypot triggered');
        }

        if (($data['consent_accepted'] ?? false) !== true) {
            throw new \DomainException('Требуется согласие на обработку персональных данных.');
        }

        $captchaOk = ($this->verifyTurnstile)(
            $data['turnstile_token'] ?? null,
            $request->ip()
        );
        if (!$captchaOk) {
            throw new \DomainException('Проверка антибота не пройдена. Обновите страницу и попробуйте снова.');
        }

        $landingUrl = $data['landing_url'] ?? null;

        $lead = ContactRequest::create([
            'name' => trim((string) $data['name']),
            'phone' => trim((string) $data['phone']),
            'email' => isset($data['email']) && $data['email'] !== null ? trim((string) $data['email']) : null,
            'message' => isset($data['message']) && $data['message'] !== null ? trim((string) $data['message']) : null,
            'consent_accepted' => true,
            'consent_text_hash' => hash('sha256', (string) $this->contactSettings->personal_data_consent_text),
            'utm' => $this->extractUtm($landingUrl),
            'referer_url' => mb_substr((string) $request->header('Referer', ''), 0, 512) ?: null,
            'landing_url' => $landingUrl !== null ? mb_substr($landingUrl, 0, 512) : null,
            'ip_hash' => $request->ip() !== null ? hash('sha256', (string) $request->ip()) : null,
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
            'status' => ContactRequestStatus::New,
        ]);

        $to = $this->integrationSettings->notify_email;
        if (is_string($to) && $to !== '') {
            Mail::to($to)->queue(new NewContactRequestMail($lead));
        }

        return $lead;
    }

    /**
     * Parse UTM params out of the original landing URL (captured on page mount).
     * The Livewire submit request itself posts to /livewire/update and therefore
     * never carries UTM query params.
     *
     * @return array<string,string>
     */
    private function extractUtm(?string $landingUrl): array
    {
        if ($landingUrl === null || $landingUrl === '') {
            return [];
        }

        $query = parse_url($landingUrl, PHP_URL_QUERY);
        if (!is_string($query) || $query === '') {
            return [];
        }

        parse_str($query, $params);

        $utm = [];
        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'] as $key) {
            $value = $params[$key] ?? null;
            if (is_string($value) && $value !== '') {
                $utm[$key] = mb_substr($value, 0, 255);
            }
        }

        return $utm;
    }
}
