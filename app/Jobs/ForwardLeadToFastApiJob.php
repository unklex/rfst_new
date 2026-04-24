<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ContactRequestStatus;
use App\Models\ContactRequest;
use App\Settings\IntegrationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Forwards a freshly created ContactRequest row to the FastAPI / CRM
 * endpoint configured in IntegrationSettings.
 *
 * Runtime behaviour:
 *   - Dispatched from SubmitContactRequestAction via `dispatch()->afterResponse()`
 *     so the HTTP request returns immediately and the POST fires after the
 *     response has been flushed (QUEUE_CONNECTION=sync compatible, no worker required).
 *   - handle() never re-throws — failures update the row's status to `Failed`
 *     and log to Sentry/the Laravel log. This keeps the user-facing submit
 *     flow clean: they always see the success panel, the admin panel shows
 *     which leads need a manual resend.
 *   - If queue driver is later switched to `database` or `redis`, the 3-retry
 *     backoff (30s / 2m / 10m) activates automatically.
 */
final class ForwardLeadToFastApiJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public readonly int $contactRequestId,
    ) {
    }

    /** Backoff schedule applies only under async queue drivers. */
    public function backoff(): array
    {
        return [30, 120, 600];
    }

    public function handle(IntegrationSettings $settings): void
    {
        $lead = ContactRequest::query()->find($this->contactRequestId);
        if ($lead === null) {
            return;
        }

        $url = is_string($settings->fastapi_lead_url) && $settings->fastapi_lead_url !== ''
            ? $settings->fastapi_lead_url
            : null;

        if ($url === null) {
            // No destination configured — admin hasn't filled the endpoint.
            // Leave status = New so ContactRequestResource still shows the lead
            // and the editor can triage manually.
            return;
        }

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->when(
                    is_string($settings->fastapi_auth_token) && $settings->fastapi_auth_token !== '',
                    fn ($http) => $http->withToken((string) $settings->fastapi_auth_token),
                )
                ->post($url, $this->buildPayload($lead));
        } catch (Throwable $e) {
            // Network error, timeout, DNS failure — mark as Failed and report.
            $this->recordFailure($lead, 0, ['error' => $e->getMessage()]);
            report($e);

            return;
        }

        $decoded = $this->decodeResponse($response->body());

        $lead->fastapi_status_code = $response->status();
        $lead->fastapi_response = $decoded;

        $externalId = $decoded['external_id'] ?? $decoded['id'] ?? null;
        if (is_string($externalId) || is_int($externalId)) {
            $lead->external_id = (string) $externalId;
        }

        if ($response->successful()) {
            $lead->status = ContactRequestStatus::Forwarded;
            $lead->forwarded_at = Carbon::now();
            $lead->save();

            return;
        }

        // Non-2xx from FastAPI — save the response body for forensics,
        // flip to Failed, log (but don't throw — sync mode would propagate
        // into the HTTP response).
        $lead->status = ContactRequestStatus::Failed;
        $lead->save();

        Log::warning('FastAPI forwarder rejected lead', [
            'lead_id' => $lead->id,
            'status' => $response->status(),
            'body' => $decoded,
        ]);
    }

    /**
     * Laravel calls this on async queue drivers when all retries have been
     * exhausted. Under sync + afterResponse we instead handle failures inline
     * in handle(), so this is a belt-and-braces safety net for the async case.
     */
    public function failed(Throwable $exception): void
    {
        $lead = ContactRequest::query()->find($this->contactRequestId);
        if ($lead === null) {
            return;
        }

        $lead->status = ContactRequestStatus::Failed;
        $lead->save();

        report($exception);
    }

    /**
     * @return array<string,mixed>
     */
    private function buildPayload(ContactRequest $lead): array
    {
        return [
            'source' => parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'crypton',
            'lead' => [
                'name' => $lead->name,
                'phone' => $lead->phone,
                'email' => $lead->email,
                'message' => $lead->message,
                'consent_text_hash' => $lead->consent_text_hash,
                'consent_accepted_at' => $lead->created_at?->toIso8601String(),
            ],
            'tracking' => [
                'utm_source' => $lead->utm['utm_source'] ?? $lead->utm['source'] ?? null,
                'utm_medium' => $lead->utm['utm_medium'] ?? $lead->utm['medium'] ?? null,
                'utm_campaign' => $lead->utm['utm_campaign'] ?? $lead->utm['campaign'] ?? null,
                'utm_content' => $lead->utm['utm_content'] ?? $lead->utm['content'] ?? null,
                'utm_term' => $lead->utm['utm_term'] ?? $lead->utm['term'] ?? null,
                'referer' => $lead->referer_url,
                'landing_url' => $lead->landing_url,
                'ip_hash' => $lead->ip_hash,
                'user_agent' => $lead->user_agent,
            ],
            'submitted_at' => $lead->created_at?->toIso8601String(),
        ];
    }

    /** @return array<string,mixed> */
    private function decodeResponse(string $body): array
    {
        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : ['raw' => $body];
    }

    /** @param array<string,mixed> $response */
    private function recordFailure(ContactRequest $lead, int $status, array $response): void
    {
        $lead->fastapi_status_code = $status;
        $lead->fastapi_response = $response;
        $lead->status = ContactRequestStatus::Failed;
        $lead->save();
    }
}
