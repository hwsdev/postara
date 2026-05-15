<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\Mail\Mailer;
use Illuminate\View\View;
use Livewire\Component;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class MailTestSend extends Component
{
    public string $toEmail = '';
    public string $toName  = '';
    public ?string $result = null;

    public function mount(): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if ($user instanceof \App\Models\User) {
            $this->toEmail = $user->email;
            $this->toName  = $user->name;
        }
    }

    public function send(): void
    {
        $this->validate([
            'toEmail' => ['required', 'email'],
            'toName'  => ['nullable', 'string', 'max:255'],
        ]);

        $this->result = null;

        try {
            $mailerName = Setting::get('mail_mailer', 'smtp');
            $fromAddr   = (string) Setting::get('mail_from_address', '');
            $fromName   = (string) Setting::get('mail_from_name', 'Postara');
            $appName    = (string) Setting::get('app_name', config('app.name', 'Postara'));
            $toEmail    = $this->toEmail;
            $toName     = $this->toName ?: $this->toEmail;

            // Build a fresh Symfony transport from DB settings.
            // This bypasses Laravel's cached Mail singleton which still uses .env config.
            $symfonyTransport = $this->buildTransport($mailerName);

            // Wrap in a fresh Laravel Mailer instance — pass transport directly
            $mailer = new Mailer(
                'postara-test',
                app('view'),
                $symfonyTransport,
                app('events')
            );

            $html = view('emails.test-send', compact('toName', 'appName'))->render();

            $mailer->html($html, function ($msg) use ($toEmail, $toName, $fromAddr, $fromName, $appName) {
                $msg->to($toEmail, $toName)
                    ->from($fromAddr, $fromName)
                    ->subject("Test email from {$appName}");
            });

            $this->result = 'success';

        } catch (\Throwable $e) {
            $this->result = 'error:' . $e->getMessage();
        }
    }

    /**
     * Build a fresh Symfony transport directly from DB settings.
     */
    private function buildTransport(string $mailerName): TransportInterface
    {
        // Log / null mode — no actual sending
        if ($mailerName === 'log') {
            return new NullTransport();
        }

        // MailChannels HTTP transport
        if ($mailerName === 'mailchannels') {
            $key = (string) Setting::get('mailchannels_api_key', '');
            if (empty($key)) {
                throw new \RuntimeException('MailChannels API key is not configured.');
            }
            config(['services.mailchannels.api_key' => $key]);
            return app(\App\Services\MailChannelsTransport::class);
        }

        // SMTP (covers both relay and self-hosted Postfix)
        $host       = (string) Setting::get('mail_host', '127.0.0.1');
        $port       = (int)    Setting::get('mail_port', 587);
        $username   = (string) Setting::get('mail_username', '');
        $password   = (string) Setting::get('mail_password', '');
        $encryption = (string) Setting::get('mail_encryption', '');

        // Symfony EsmtpTransport $tls parameter:
        //   true  = implicit SSL (port 465, ssl:// prefix)
        //   false = plain / STARTTLS negotiation (port 587 or 25)
        //   null  = auto-detect (true if port 465, false otherwise)
        $tls = match ($encryption) {
            'ssl'  => true,   // port 465 — implicit SSL
            'tls'  => false,  // port 587 — STARTTLS (negotiated after connect)
            default => null,  // auto-detect
        };

        $transport = new EsmtpTransport($host, $port, $tls);

        if (! empty($username)) {
            $transport->setUsername($username);
            $transport->setPassword($password);
        }

        return $transport;
    }

    public function render(): View
    {
        return view('livewire.settings.mail-test-send');
    }
}
