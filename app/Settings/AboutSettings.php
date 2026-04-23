<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AboutSettings extends Settings
{
    public string $section_index;
    public string $section_kicker;
    public string $section_heading_html;
    public string $legal_block_html;

    public string $body_heading_html;
    public string $body_paragraph;

    public string $cta_label;
    public string $cta_url;

    public static function group(): string
    {
        return 'about';
    }
}
