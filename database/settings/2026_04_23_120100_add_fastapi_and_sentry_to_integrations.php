<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->addEncrypted('integrations.fastapi_auth_token', null);
        $this->migrator->add('integrations.fastapi_lead_url', null);
        $this->migrator->add('integrations.sentry_dsn', null);
    }

    public function down(): void
    {
        $this->migrator->delete('integrations.fastapi_auth_token');
        $this->migrator->delete('integrations.fastapi_lead_url');
        $this->migrator->delete('integrations.sentry_dsn');
    }
};
