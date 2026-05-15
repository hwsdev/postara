<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\ApiKey;
use App\Models\Email;
use App\Models\Suppression;
use App\Models\Workspace;
use Illuminate\Support\Str;

class EmailService
{
    public function __construct(
        private readonly TrackingService $trackingService
    ) {}

    /**
     * Queue a transactional email for sending.
     */
    public function send(Workspace $workspace, array $data): Email
    {
        // Check idempotency key
        if (! empty($data['idempotency_key'])) {
            $existing = Email::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                return $existing;
            }
        }

        // Filter suppressed recipients
        $recipients = (array) $data['to'];
        $suppressed = Suppression::where('workspace_id', $workspace->id)
            ->whereIn('email', $recipients)
            ->pluck('email')
            ->toArray();

        $filteredTo = array_values(array_diff($recipients, $suppressed));

        if (empty($filteredTo)) {
            throw new \RuntimeException('All recipients are suppressed.');
        }

        $email = Email::create([
            'workspace_id' => $workspace->id,
            'from' => $data['from'],
            'to' => $filteredTo,
            'cc' => $data['cc'] ?? null,
            'bcc' => $data['bcc'] ?? null,
            'subject' => $data['subject'],
            'html' => $data['html'] ?? null,
            'text' => $data['text'] ?? null,
            'template_id' => $data['template_id'] ?? null,
            'tags' => $data['tags'] ?? null,
            'headers' => $data['headers'] ?? null,
            'idempotency_key' => $data['idempotency_key'] ?? null,
            'status' => 'queued',
        ]);

        SendEmailJob::dispatch($email);

        return $email;
    }

    /**
     * Resolve template variables and render HTML.
     */
    public function renderTemplate(\App\Models\Template $template, array $variables = []): string
    {
        $html = $template->html ?? '';

        // Simple Blade-like variable replacement
        foreach ($variables as $key => $value) {
            $html = str_replace('{{ $'.$key.' }}', e($value), $html);
            $html = str_replace('{{'.$key.'}}', e($value), $html);
        }

        return $html;
    }
}
