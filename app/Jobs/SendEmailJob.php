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

            // Add CC/BCC if present
            $mailer = Mail::to($email->to);

            if (! empty($email->cc)) {
                $mailer = $mailer->cc($email->cc);
            }

            if (! empty($email->bcc)) {
                $mailer = $mailer->bcc($email->bcc);
            }

            $mailer->send($mailable);

            // Use the message ID assigned by the mailer if available
            $messageId = $mailable->getSymfonyMessage()?->getHeaders()->get('Message-ID')?->getBodyAsString()
                ?? (string) \Illuminate\Support\Str::uuid().'@postara';

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

            $webhookService->dispatch(
                $email->workspace,
                'email.failed',
                $webhookService->buildEmailPayload($email, 'email.failed', ['error' => $e->getMessage()])
            );

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->email->update(['status' => 'failed']);
    }
}
