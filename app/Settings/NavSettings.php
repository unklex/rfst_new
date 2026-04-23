<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class NavSettings extends Settings
{
    public string $phone_number;
    public string $phone_label;
    public string $primary_cta_label;

    public static function group(): string
    {
        return 'nav';
    }
}
