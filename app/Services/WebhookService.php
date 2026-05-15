<?php

namespace App\Services;

use App\Jobs\DeliverWebhookJob;
use App\Models\Email;
use App\Models\Webhook;
use App\Models\Workspace;

class WebhookService
{
    /**
     * Dispatch webhook deliveries for a given event.
     */
    public function dispatch(Workspace $workspace, string $event, array $payload): void
    {
        $webhooks = Webhook::where('workspace_id', $workspace->id)
            ->where('active', true)
            ->whereJsonContains('events', $event)
            ->get();

        foreach ($webhooks as $webhook) {
            DeliverWebhookJob::dispatch($webhook, $event, $payload);
        }
    }

    /**
     * Generate HMAC-SHA256 signature for a payload.
     */
    public function sign(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Build a standard webhook payload for an email event.
     */
    public function buildEmailPayload(Email $email, string $event, array $extra = []): array
    {
        return array_merge([
            'event' => $event,
            'created_at' => now()->toIso8601String(),
            'data' => [
                'email_id' => $email->id,
                'message_id' => $email->message_id,
                'from' => $email->from,
                'to' => $email->to,
                'subject' => $email->subject,
                'status' => $email->status,
                'tags' => $email->tags,
            ],
        ], $extra);
    }
}
