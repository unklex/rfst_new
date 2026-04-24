<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Report all unhandled exceptions to Sentry. Works no-op if no DSN is
        // configured (sentry-laravel checks config('sentry.dsn') at report time).
        // DSN resolution priority: IntegrationSettings::sentry_dsn (runtime, admin-editable)
        // → env('SENTRY_LARAVEL_DSN') → null (→ no-op). Runtime override lives in
        // App\Providers\AppServiceProvider::register().
        \Sentry\Laravel\Integration::handles($exceptions);
    })->create();
