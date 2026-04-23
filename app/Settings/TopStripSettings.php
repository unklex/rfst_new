<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TopStripSettings extends Settings
{
    public string $status_text;
    public string $location_text;
    public string $license_text;
    public string $lang_label;
    public ?string $telegram_url;
    public ?string $whatsapp_url;

    public static function group(): string
    {
        return 'strip';
    }
}
