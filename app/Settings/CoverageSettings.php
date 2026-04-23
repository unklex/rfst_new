<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CoverageSettings extends Settings
{
    public string $kicker;
    public string $heading_html;
    public string $paragraph;
    public string $map_meta_html;

    public static function group(): string
    {
        return 'coverage';
    }
}
