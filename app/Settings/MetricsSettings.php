<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MetricsSettings extends Settings
{
    public string $header_html;
    public string $stamp_text;

    public static function group(): string
    {
        return 'metrics';
    }
}
