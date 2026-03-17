<?php

namespace App\Jobs;

use App\Contracts\Webhooks\WebhookEventContract;
use App\Models\WebhookEndpoint;
use App\Services\Webhooks\WebhookSigner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class DispatchWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $maxExceptions = 3;

    public function __construct(
        public readonly WebhookEndpoint $endpoint,
        public readonly WebhookEventContract $event,
    ) {}

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [60, 120, 300, 600, 1800];
    }

    public function handle(WebhookSigner $signer): void
    {
        $payload = json_encode([
            'event'     => $this->event->eventName(),
            'payload'   => $this->event->payload(),
            'timestamp' => now()->toIso8601String(),
        ]);

        $signature = $signer->sign($payload, $this->endpoint->secret);

        $response = Http::withHeaders([
            'Content-Type'         => 'application/json',
            'X-Webhook-Event'      => $this->event->eventName(),
            'X-Webhook-Signature'  => $signature,
        ])->timeout(10)->post($this->endpoint->url, json_decode($payload, true));

        $this->endpoint->update(['last_triggered_at' => now()]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                "Webhook delivery failed for {$this->endpoint->url}: HTTP {$response->status()}"
            );
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->endpoint->increment('failure_count');

        if ($this->endpoint->fresh()->failure_count >= 10) {
            $this->endpoint->update(['is_active' => false]);
        }
    }
}
