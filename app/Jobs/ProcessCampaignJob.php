<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Email;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(
        public readonly Campaign $campaign
    ) {}

    public function handle(EmailService $emailService): void
    {
        $campaign = $this->campaign;

        if (! in_array($campaign->status, ['scheduled', 'sending'])) {
            return;
        }

        $campaign->update(['status' => 'sending']);

        $template = $campaign->template;
        $contactList = $campaign->contactList;

        if (! $template || ! $contactList) {
            $campaign->update(['status' => 'cancelled']);

            return;
        }

        // Get contacts, respecting suppression list
        $suppressedEmails = $campaign->workspace->suppressions()->pluck('email')->toArray();

        $contacts = $contactList->contacts()
            ->where('subscribed', true)
            ->whereNotIn('email', $suppressedEmails)
            ->cursor();

        foreach ($contacts as $contact) {
            $html = $emailService->renderTemplate($template, [
                'name' => $contact->name ?? $contact->email,
                'email' => $contact->email,
            ]);

            $email = Email::create([
                'workspace_id' => $campaign->workspace_id,
                'from' => "{$campaign->from_name} <{$campaign->from_email}>",
                'to' => [$contact->email],
                'subject' => $campaign->subject,
                'html' => $html,
                'template_id' => $template->id,
                'campaign_id' => $campaign->id,
                'status' => 'queued',
            ]);

            SendEmailJob::dispatch($email);
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->campaign->update(['status' => 'failed']);
    }
}
