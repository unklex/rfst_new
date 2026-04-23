<?php

declare(strict_types=1);

namespace App\Actions\Contact;

use App\Settings\IntegrationSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class VerifyTurnstileAction
{
    private const ENDPOINT = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(
        private readonly IntegrationSettings $settings,
    ) {
    }

    public function __invoke(?string $token, ?string $ip = null): bool
    {
        $secret = (string) ($this->settings->turnstile_secret_key ?? '');

        // No secret configured → dev/off mode. Fresh installs or deployments
        // where the admin has not yet pasted keys into IntegrationSettings
        // submit without captcha. Site key absence in the view also hides
        // the widget, so visitors never see a broken cf-turnstile slot.
        if ($secret === '') {
            return true;
        }

        if ($token === null || $token === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(6)
                ->post(self::ENDPOINT, array_filter([
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $ip,
                ]));
        } catch (\Throwable $e) {
            Log::warning('Turnstile verification threw', ['error' => $e->getMessage()]);

            return false;
        }

        if (!$response->successful()) {
            Log::warning('Turnstile verification HTTP error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        $ok = (bool) $response->json('success', false);
        if (!$ok) {
            Log::info('Turnstile verification failed', [
                'error_codes' => $response->json('error-codes', []),
            ]);
        }

        return $ok;
    }
}
