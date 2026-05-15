<?php

namespace App\Jobs;

use App\Models\Email;
use App\Services\TrackingService;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly Email $email
    ) {}

    public function handle(TrackingService $trackingService, WebhookService $webhookService): void
    {
        $email = $this->email;

        try {
            $email->update(['status' => 'sending']);

            $html = $email->html ?? '';

            // Instrument with tracking
            if (! empty($html)) {
                $html = $trackingService->instrument($email, $html);
            }

            // Build and send via Laravel Mail
            $mailable = new \App\Mail\TransactionalMail($email, $html);

            Mail::to($email->to)->send($mailable);

            $messageId = Str::uuid().'@postara';
            $email->update([
                'status' => 'delivered',
                'message_id' => $messageId,
            ]);

            $trackingService->record($email, 'delivered');

            $webhookService->dispatch(
                $email->workspace,
                'email.delivered',
                $webhookService->buildEmailPayload($email, 'email.delivered')
            );
        } catch (\Exception $e) {
            $email->update(['status' => 'failed']);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->email->update(['status' => 'failed']);
    }
}
