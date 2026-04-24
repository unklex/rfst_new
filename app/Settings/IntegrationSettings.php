<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class IntegrationSettings extends Settings
{
    public ?string $turnstile_site_key;
    public ?string $turnstile_secret_key;
    public ?string $notify_email;
    public ?string $yandex_metrika_id;

    // FastAPI lead receiver — external CRM / webhook endpoint for pushing
    // every new contact_requests row. Runtime-editable via admin UI; leave
    // both fields empty to disable forwarding (leads still save to DB + mail).
    public ?string $fastapi_lead_url;
    public ?string $fastapi_auth_token;

    // Sentry DSN override. Priority: this field → env('SENTRY_LARAVEL_DSN')
    // → null (SDK no-ops). Lets an admin rotate or disable without a deploy.
    public ?string $sentry_dsn;

    public static function group(): string
    {
        return 'integrations';
    }

    public static function encrypted(): array
    {
        return [
            'turnstile_secret_key',
            'fastapi_auth_token',
        ];
    }
}
