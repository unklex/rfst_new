<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Contact\SubmitContactRequestAction;
use App\Actions\Contact\VerifyTurnstileAction;
use App\Livewire\ContactForm;
use App\Settings\ContactSettings;
use App\Settings\IntegrationSettings;
use Illuminate\Http\Request;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    public function test_tampered_array_honeypot_payload_is_treated_as_bot_submission(): void
    {
        $component = new ContactForm();
        $component->website = ['bot'];
        $component->error = ['tampered'];

        $component->submit($this->submitAction(), Request::create('/livewire/update', 'POST'));

        $this->assertTrue($component->submitted);
        $this->assertNull($component->error);
    }

    private function submitAction(): SubmitContactRequestAction
    {
        $integrationSettings = new IntegrationSettings([
            'turnstile_site_key' => null,
            'turnstile_secret_key' => null,
            'notify_email' => null,
            'yandex_metrika_id' => null,
            'fastapi_lead_url' => null,
            'fastapi_auth_token' => null,
            'sentry_dsn' => null,
        ]);

        return new SubmitContactRequestAction(
            new VerifyTurnstileAction($integrationSettings),
            new ContactSettings([
                'heading_html' => '',
                'address' => '',
                'phone' => '',
                'email' => '',
                'hours' => '',
                'messengers' => '',
                'form_label_name' => '',
                'form_label_phone' => '',
                'form_label_email' => '',
                'form_label_message' => '',
                'form_placeholder_name' => '',
                'form_placeholder_phone' => '',
                'form_placeholder_email' => '',
                'form_placeholder_message' => '',
                'form_submit_label' => '',
                'form_consent_text' => '',
                'personal_data_consent_text' => '',
            ]),
            $integrationSettings,
        );
    }
}
