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
        // ─── /livewire/update noise suppression ────────────────────────────────
        // Bots replay/probe/tamper with Livewire payloads and stale browser tabs
        // post outdated snapshots. These throw on every hit but affect 0 real
        // users. For each we skip Sentry reporting and return a clean response.
        //
        // ORDERING CONTRACT — read before touching this block:
        // Laravel runs report callbacks in registration order and bails on the
        // FIRST one that returns false (Handler::reportThrowable, foreach over
        // $reportCallbacks). Sentry registers its reporter as a reportable callback
        // inside Integration::handles(). So any *conditional* report-suppression
        // callback that returns false MUST be registered BEFORE Integration::handles()
        // below — otherwise Sentry's callback runs first and the event is already
        // sent before our `false` short-circuits. (dontReport() is exempt: it's
        // checked in shouldntReport() before any callbacks run, so order-independent.)

        // Unknown component name (e.g. "page.component") — bot probe.
        $exceptions->dontReport(\Livewire\Exceptions\ComponentNotFoundException::class);
        // Checksum mismatch over [name, id, data] — tampered or stale snapshot.
        $exceptions->dontReport(\Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException::class);

        // Filament\Notifications\Collection::fromLivewire() has a strict `array`
        // type hint on its closure (vendor/filament/notifications/src/Collection.php:32).
        // Malformed snapshots pass ints instead of arrays → PHP TypeError. We can't
        // blanket-dontReport TypeError, so suppress only this one by message.
        // MUST stay above Integration::handles() — see ordering contract above.
        $exceptions->report(function (\TypeError $e): ?bool {
            return str_contains($e->getMessage(), 'Filament\\Notifications\\Collection')
                ? false  // stop reporting; Sentry's callback (registered later) never runs
                : null;  // fall through to Sentry + default logging
        });

        // Register Sentry AFTER the conditional suppressor above so its `false`
        // short-circuits before Sentry captures. Works no-op if no DSN is configured
        // (sentry-laravel checks config('sentry.dsn') at report time). DSN resolution
        // priority: IntegrationSettings::sentry_dsn (runtime, admin-editable) →
        // env('SENTRY_LARAVEL_DSN') → null. Runtime override lives in AppServiceProvider.
        \Sentry\Laravel\Integration::handles($exceptions);

        // ─── Clean responses (render order is independent of reporting order) ──
        $exceptions->render(function (\Livewire\Exceptions\ComponentNotFoundException $e) {
            return response()->json(['message' => 'Not Found'], 404);
        });
        // 419 → Livewire's client treats it as session-expired and prompts a refresh,
        // so a real stale tab recovers gracefully instead of hard-failing.
        $exceptions->render(function (\Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException $e) {
            return response()->json(['message' => 'Page Expired'], 419);
        });
        $exceptions->render(function (\TypeError $e): ?\Illuminate\Http\JsonResponse {
            return str_contains($e->getMessage(), 'Filament\\Notifications\\Collection')
                ? response()->json(['message' => 'Not Found'], 404)
                : null;
        });
    })->create();
