<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class HeroSettings extends Settings
{
    public string $ref_code_html;
    public string $hazard_label;
    public string $headline_html;
    public string $lede_html;

    public string $cta_primary_label;
    public string $cta_primary_anchor;
    public string $cta_secondary_label;
    public string $cta_secondary_anchor;

    public string $signature_name;
    public string $signature_caption_html;

    // Card A (light, left) — the "10+ лет" stat card
    public string $card_a_kicker;
    public string $card_a_big_value;
    public string $card_a_big_suffix;
    public string $card_a_label_strong;
    public string $card_a_label_text;
    public string $card_a_stat1_value;
    public string $card_a_stat1_label;
    public string $card_a_stat2_value;
    public string $card_a_stat2_label;
    public string $card_a_stat3_value;
    public string $card_a_stat3_label;

    // Card B (dark, bottom) — the license card
    public string $card_b_kicker;
    public string $card_b_title_html;
    public string $card_b_license_number;
    public string $card_b_license_detail;
    public string $card_b_class_label;
    public string $card_b_class_value;

    public static function group(): string
    {
        return 'hero';
    }
}
