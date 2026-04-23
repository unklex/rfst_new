<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;
    public string $tagline;
    public string $meta_description;
    public string $brand_wordmark;
    public string $brand_wordmark_accent_char;
    public string $brand_mark_letter;
    public string $brand_subtitle;

    public static function group(): string
    {
        return 'general';
    }
}
