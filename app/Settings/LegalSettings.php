<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class LegalSettings extends Settings
{
    public string $legal_name;
    public string $inn;
    public ?string $kpp;
    public ?string $ogrn;
    public string $license_number;
    public string $license_issuer;
    public string $license_date;

    public static function group(): string
    {
        return 'legal';
    }
}
