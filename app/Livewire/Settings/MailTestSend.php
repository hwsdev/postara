<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Livewire\Component;

class MailTestSend extends Component
{
    public string $toEmail = '';
    public string $toName = '';
    public ?string $result = null;

    public function mount(): void
    {
        $this->toEmail = auth()->user()->email;
        $this->toName  = auth()->user()->name;
    }

    public function send(): void
    {
        $this->validate([
            'toEmail' => ['required', 'email'],
            'toName'  => ['nullable', 'string', 'max:255'],
        ]);

        $this->result = null;

        try {
            // Apply saved mail config at runtime
            $mailer   = Setting::get('mail_mailer', config('mail.default'));
            $fromAddr = Setting::get('mail_from_address', config('mail.from.address'));
            $fromName = Setting::get('mail_from_name', config('mail.from.name'));

            config([
                'mail.default'                 => $mailer,
                'mail.mailers.smtp.host'       => Setting::get('mail_host', config('mail.mailers.smtp.host')),
                'mail.mailers.smtp.port'       => Setting::get('mail_port', config('mail.mailers.smtp.port')),
                'mail.mailers.smtp.username'   => Setting::get('mail_username', config('mail.mailers.smtp.username')),
                'mail.mailers.smtp.password'   => Setting::get('mail_password', config('mail.mailers.smtp.password')),
                'mail.mailers.smtp.encryption' => Setting::get('mail_encryption', config('mail.mailers.smtp.encryption')),
                'mail.from.address'            => $fromAddr,
                'mail.from.name'               => $fromName,
            ]);

            $toEmail = $this->toEmail;
            $toName  = $this->toName ?: $this->toEmail;
            $appName = Setting::get('app_name', config('app.name', 'Postara'));

            Mail::html(
                view('emails.test-send', compact('toName', 'appName'))->render(),
                function ($message) use ($toEmail, $toName, $fromAddr, $fromName, $appName) {
                    $message->to($toEmail, $toName)
                            ->from($fromAddr, $fromName)
                            ->subject("Test email from {$appName}");
                }
            );

            $this->result = 'success';
        } catch (\Throwable $e) {
            $this->result = 'error:' . $e->getMessage();
        }
    }

    public function render(): View
    {
        return view('livewire.settings.mail-test-send');
    }
}
