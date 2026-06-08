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

        // Bots probe /livewire/update with arbitrary component names. Return 404
        // instead of 500 and skip Sentry reporting — nothing actionable here.
        $exceptions->dontReport(\Livewire\Exceptions\ComponentNotFoundException::class);
        $exceptions->render(function (\Livewire\Exceptions\ComponentNotFoundException $e) {
            return response()->json(['message' => 'Not Found'], 404);
        });

        // Filament\Notifications\Collection::fromLivewire() has a strict `array`
        // type hint on its closure (vendor/filament/notifications/src/Collection.php:32).
        // Bots / stale sessions send malformed Livewire snapshots where notification
        // items are ints instead of arrays → PHP TypeError. 0 real users affected.
        // Suppress Sentry noise and return 404 — not actionable until Filament fixes upstream.
        $exceptions->report(function (\TypeError $e): ?bool {
            if (str_contains($e->getMessage(), 'Filament\\Notifications\\Collection')) {
                return false; // stops further reporting (including Sentry)
            }

            return null; // fall through to default handling
        });
        $exceptions->render(function (\TypeError $e): ?\Illuminate\Http\JsonResponse {
            if (str_contains($e->getMessage(), 'Filament\\Notifications\\Collection')) {
                return response()->json(['message' => 'Not Found'], 404);
            }

            return null; // fall through to default rendering
        });

        // Livewire verifies a checksum over each component's [name, id, data] snapshot.
        // Bots replaying/tampering with /livewire/update payloads — and genuinely stale
        // browser tabs — fail that check and throw CorruptComponentPayloadException.
        // 0 real users affected. Skip Sentry reporting and return 419 (Page Expired):
        // Livewire's client treats 419 as a session-expired signal and prompts a refresh,
        // so a real stale tab recovers gracefully instead of hard-failing.
        $exceptions->dontReport(\Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException::class);
        $exceptions->render(function (\Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException $e) {
            return response()->json(['message' => 'Page Expired'], 419);
        });
    })->create();
