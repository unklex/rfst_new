<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ContactSettings extends Settings
{
    public string $heading_html;

    // Info rows (left column)
    public string $address;
    public string $phone;
    public string $email;
    public string $hours;
    public string $messengers;

    // Form labels + placeholders
    public string $form_label_name;
    public string $form_label_phone;
    public string $form_label_email;
    public string $form_label_message;
    public string $form_placeholder_name;
    public string $form_placeholder_phone;
    public string $form_placeholder_email;
    public string $form_placeholder_message;

    public string $form_submit_label;
    public string $form_consent_text;
    public string $personal_data_consent_text;

    public static function group(): string
    {
        return 'contact';
    }
}
