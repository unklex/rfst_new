<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class IntegrationSettings extends Settings
{
    public ?string $turnstile_site_key;
    public ?string $turnstile_secret_key;
    public ?string $notify_email;
    public ?string $yandex_metrika_id;

    public static function group(): string
    {
        return 'integrations';
    }

    public static function encrypted(): array
    {
        return ['turnstile_secret_key'];
    }
}
