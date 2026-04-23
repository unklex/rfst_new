<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class QuoteSettings extends Settings
{
    public string $reviewer_name;
    public string $reviewer_role;
    public string $reviewer_ref;
    public string $quote_html;
    public string $company_name;
    public string $company_description;

    public static function group(): string
    {
        return 'quote';
    }
}
