<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CtaBandSettings extends Settings
{
    public string $heading_html;
    public string $paragraph;
    public string $cta_primary_label;
    public string $cta_primary_url;
    public string $cta_secondary_label;
    public string $cta_secondary_url;

    public static function group(): string
    {
        return 'cta_band';
    }
}
