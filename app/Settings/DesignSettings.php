<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class DesignSettings extends Settings
{
    /** hazard | iron | indigo | blood */
    public string $signal;

    /** bone | fog | noir */
    public string $paper;

    /** serif | sans */
    public string $head_weight;

    public static function group(): string
    {
        return 'design';
    }
}
