<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [60, 300, 900, 3600, 7200];

    public function __construct(
        public readonly Webhook $webhook,
        public readonly string $eventType,
        public readonly array $payload
    ) {}

    public function handle(WebhookService $webhookService): void
    {
        $payloadJson = json_encode($this->payload);
        $signature = $webhookService->sign($payloadJson, $this->webhook->secret);

        $delivery = WebhookDelivery::create([
            'webhook_id' => $this->webhook->id,
            'event_type' => $this->eventType,
            'payload' => $this->payload,
            'attempt' => $this->attempts(),
            'status' => 'pending',
        ]);

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Postara-Signature' => "sha256={$signature}",
                    'X-Postara-Event' => $this->eventType,
                ])
                ->post($this->webhook->url, $this->payload);

            $delivery->update([
                'response_status' => $response->status(),
                'response_body' => substr($response->body(), 0, 1000),
                'status' => $response->successful() ? 'delivered' : 'failed',
            ]);

            $this->webhook->update(['last_triggered_at' => now()]);

            if (! $response->successful()) {
                throw new \RuntimeException("Webhook delivery failed with status {$response->status()}");
            }
        } catch (\Exception $e) {
            $delivery->update(['status' => 'failed']);
            throw $e;
        }
    }
}
