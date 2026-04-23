<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class FooterSettings extends Settings
{
    public string $about_paragraph;
    public string $copyright_html;

    public string $legal_link_policy_label;
    public string $legal_link_policy_url;
    public string $legal_link_oferta_label;
    public string $legal_link_oferta_url;
    public string $legal_link_152fz_label;
    public string $legal_link_152fz_url;

    public string $massive_wordmark;
    public string $massive_italic_char;

    public static function group(): string
    {
        return 'footer';
    }
}
