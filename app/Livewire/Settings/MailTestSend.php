<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Log;
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
    public array $debugLog = [];

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

        $this->result   = null;
        $this->debugLog = [];

        try {
            // ── Step 1: Read config from DB ───────────────────────────
            $mailerName = (string) Setting::get('mail_mailer', 'smtp');
            $fromAddr   = (string) Setting::get('mail_from_address', '');
            $fromName   = (string) Setting::get('mail_from_name', 'Postara');
            $appName    = (string) Setting::get('app_name', config('app.name', 'Postara'));
            $toEmail    = $this->toEmail;
            $toName     = $this->toName ?: $this->toEmail;

            $this->log('info', 'Config loaded from DB', [
                'mailer'       => $mailerName,
                'from_address' => $fromAddr,
                'from_name'    => $fromName,
                'to'           => $toEmail,
            ]);

            if (empty($fromAddr)) {
                throw new \RuntimeException('From address is not configured. Go to Settings and set a From address.');
            }

            // ── Step 2: Build transport ───────────────────────────────
            $this->log('info', 'Building transport…', ['type' => $mailerName]);
            $transport = $this->buildTransport($mailerName);
            $this->log('success', 'Transport built', ['class' => class_basename($transport)]);

            // ── Step 3: Build mailer ──────────────────────────────────
            $mailer = new Mailer(
                'postara-test',
                app('view'),
                $transport,
                app('events')
            );
            $this->log('info', 'Mailer instance created');

            // ── Step 4: Render email ──────────────────────────────────
            $html = view('emails.test-send', compact('toName', 'appName'))->render();
            $this->log('info', 'Email HTML rendered', ['bytes' => strlen($html)]);

            // ── Step 5: Send ──────────────────────────────────────────
            $this->log('info', 'Sending…', [
                'to'      => $toEmail,
                'subject' => "Test email from {$appName}",
            ]);

            $mailer->html($html, function ($msg) use ($toEmail, $toName, $fromAddr, $fromName, $appName) {
                $msg->to($toEmail, $toName)
                    ->from($fromAddr, $fromName)
                    ->subject("Test email from {$appName}");
            });

            $this->log('success', 'Accepted by mail server — delivery in progress');
            $this->result = 'success';

            // Also write to Laravel log
            Log::info('[MailTestSend] Test email sent', [
                'to'     => $toEmail,
                'mailer' => $mailerName,
                'from'   => $fromAddr,
            ]);

        } catch (\Throwable $e) {
            $this->log('error', $e->getMessage(), [
                'class' => class_basename($e),
                'file'  => basename($e->getFile()) . ':' . $e->getLine(),
            ]);
            $this->result = 'error:' . $e->getMessage();

            Log::error('[MailTestSend] Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function log(string $level, string $message, array $context = []): void
    {
        $this->debugLog[] = [
            'level'   => $level,   // 'info' | 'success' | 'error'
            'message' => $message,
            'context' => $context,
            'time'    => now()->format('H:i:s.v'),
        ];
    }

    private function buildTransport(string $mailerName): TransportInterface
    {
        if ($mailerName === 'log') {
            $this->log('info', 'Log mode — emails written to laravel.log, not sent');
            return new NullTransport();
        }

        if ($mailerName === 'mailchannels') {
            $key = (string) Setting::get('mailchannels_api_key', '');
            if (empty($key)) {
                throw new \RuntimeException('MailChannels API key is not configured.');
            }
            $this->log('info', 'Using MailChannels HTTP transport');
            config(['services.mailchannels.api_key' => $key]);
            return app(\App\Services\MailChannelsTransport::class);
        }

        // SMTP
        $host       = (string) Setting::get('mail_host', '127.0.0.1');
        $port       = (int)    Setting::get('mail_port', 587);
        $username   = (string) Setting::get('mail_username', '');
        $password   = (string) Setting::get('mail_password', '');
        $encryption = (string) Setting::get('mail_encryption', '');

        $this->log('info', 'SMTP config', [
            'host'       => $host,
            'port'       => $port,
            'username'   => $username ?: '(none)',
            'encryption' => $encryption ?: 'none',
        ]);

        // Port 465 = implicit SSL. Port 587/25 = STARTTLS.
        $implicitTls = ($encryption === 'ssl' || $port === 465);

        $this->log('info', 'TLS mode', [
            'implicit_tls' => $implicitTls ? 'yes (SSL/465)' : 'no (STARTTLS/587 or plain/25)',
        ]);

        $transport = new EsmtpTransport($host, $port, $implicitTls);

        if (! empty($username)) {
            $transport->setUsername($username);
            $transport->setPassword($password);
            $this->log('info', 'Auth credentials set');
        } else {
            $this->log('info', 'No auth credentials (Postfix/open relay mode)');
        }

        // Disable peer verification for local/self-hosted
        $isLocal = empty($username)
            || in_array($host, ['127.0.0.1', 'localhost', 'postfix'])
            || str_starts_with($host, '192.168.')
            || str_starts_with($host, '10.');

        if ($isLocal) {
            /** @var \Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream $stream */
            $stream = $transport->getStream();
            $stream->setStreamOptions([
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ]);
            $this->log('info', 'SSL peer verification disabled (local/self-hosted host)');
        }

        return $transport;
    }

    public function clearLog(): void
    {
        $this->debugLog = [];
        $this->result   = null;
    }

    public function render(): View
    {
        return view('livewire.settings.mail-test-send');
    }
}
