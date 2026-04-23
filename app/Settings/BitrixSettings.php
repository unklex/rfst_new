<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class BitrixSettings extends Settings
{
    public string $kicker;
    public string $heading_html;
    public string $paragraph;

    public string $cta_label;
    public string $cta_url;

    public string $mock_url;
    public string $mock_version;
    public string $mock_footer_left;
    public string $mock_footer_right_html;
    public string $caption;

    public static function group(): string
    {
        return 'bitrix';
    }
}
