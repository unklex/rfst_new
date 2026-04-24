<?php

namespace App\Providers;

use App\Settings\IntegrationSettings;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Runtime Sentry DSN override.
        //
        // IntegrationSettings (admin UI) takes priority over env(), so admins
        // can paste or rotate the DSN without a deploy. If Settings reads fail
        // (fresh install, migrate:fresh, DB down), we fall back to env — Sentry
        // itself no-ops when the DSN stays null.
        $this->booting(function (): void {
            try {
                $dsn = app(IntegrationSettings::class)->sentry_dsn;
                if (is_string($dsn) && $dsn !== '') {
                    config(['sentry.dsn' => $dsn]);
                }
            } catch (Throwable) {
                // Settings table may not exist yet (pre-migrate) — silently
                // keep whatever config/sentry.php already loaded from env.
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
