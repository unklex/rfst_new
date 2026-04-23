<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ServicesSectionSettings extends Settings
{
    public string $section_index;
    public string $section_kicker;
    public string $section_heading_html;
    public string $section_note_html;

    public static function group(): string
    {
        return 'services_section';
    }
}
