<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\Domain;
use App\Models\Email;
use App\Models\Suppression;
use App\Models\Workspace;
use Illuminate\Support\Facades\Blade;

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

        // Extract the domain from the from address
        $fromEmail = $this->extractEmail($data['from'] ?? '');
        if ($fromEmail) {
            $fromDomain = substr(strrchr($fromEmail, '@'), 1);
            $verified = Domain::where('workspace_id', $workspace->id)
                ->where('domain', $fromDomain)
                ->where('status', 'verified')
                ->exists();

            if (! $verified) {
                throw new \RuntimeException(
                    "Domain '{$fromDomain}' is not verified. Please verify your sending domain before sending emails."
                );
            }
        }

        $email = Email::create([
            'workspace_id'    => $workspace->id,
            'from'            => $data['from'],
            'to'              => $filteredTo,
            'cc'              => $data['cc'] ?? null,
            'bcc'             => $data['bcc'] ?? null,
            'subject'         => $data['subject'],
            'html'            => $data['html'] ?? null,
            'text'            => $data['text'] ?? null,
            'template_id'     => $data['template_id'] ?? null,
            'tags'            => $data['tags'] ?? null,
            'headers'         => $data['headers'] ?? null,
            'idempotency_key' => $data['idempotency_key'] ?? null,
            'status'          => 'queued',
        ]);

        SendEmailJob::dispatch($email);

        return $email;
    }

    /**
     * Render a template with variables using Blade.
     */
    public function renderTemplate(\App\Models\Template $template, array $variables = []): string
    {
        $html = $template->html ?? '';

        if (empty($html)) {
            return '';
        }

        try {
            // Use Blade::render for proper template compilation
            return Blade::render($html, $variables, deleteCachedView: true);
        } catch (\Throwable) {
            // Fallback to simple variable replacement if Blade fails
            // (e.g. template has syntax errors or unsafe directives)
            return $this->simpleReplace($html, $variables);
        }
    }

    /**
     * Simple variable replacement fallback.
     */
    private function simpleReplace(string $html, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $escaped = e((string) $value);
            $html = str_replace('{{ $' . $key . ' }}', $escaped, $html);
            $html = str_replace('{{$' . $key . '}}', $escaped, $html);
            $html = str_replace('{{ ' . $key . ' }}', $escaped, $html);
            $html = str_replace('{{' . $key . '}}', $escaped, $html);
        }

        return $html;
    }

    /**
     * Extract plain email address from "Name <email>" or "email" format.
     */
    private function extractEmail(string $from): string
    {
        if (preg_match('/<([^>]+)>/', $from, $m)) {
            return trim($m[1]);
        }

        return trim($from);
    }
}
