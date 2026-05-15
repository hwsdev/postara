<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\View\View;
use Livewire\Component;

class GeneralSettings extends Component
{
    // Mail
    public string $mailMode = 'smtp';
    public string $mailHost = '';
    public string $mailPort = '587';
    public string $mailUsername = '';
    public string $mailPassword = '';
    public string $mailEncryption = 'tls';
    public string $postfixHost = '127.0.0.1';
    public string $postfixPort = '25';
    public string $mailChannelsApiKey = '';
    public string $mailFromAddress = '';
    public string $mailFromName = '';

    // Cloudflare
    public string $cloudflareToken = '';

    public bool $saved = false;

    public function mount(): void
    {
        $mailer = Setting::get('mail_mailer', 'smtp');

        // Map stored mailer back to UI mode
        if ($mailer === 'mailchannels') {
            $this->mailMode = 'mailchannels';
        } elseif ($mailer === 'log') {
            $this->mailMode = 'log';
        } elseif ($mailer === 'smtp') {
            $host = Setting::get('mail_host', '');
            $port = Setting::get('mail_port', '587');
            // Detect postfix: no username + port 25
            if (empty(Setting::get('mail_username', '')) && (string) $port === '25') {
                $this->mailMode = 'postfix';
                $this->postfixHost = $host ?: '127.0.0.1';
                $this->postfixPort = (string) $port;
            } else {
                $this->mailMode = 'smtp';
                $this->mailHost = $host;
                $this->mailPort = (string) $port;
                $this->mailUsername = Setting::get('mail_username', '');
                $this->mailEncryption = Setting::get('mail_encryption', 'tls');
            }
        }

        $this->mailFromAddress = Setting::get('mail_from_address', '');
        $this->mailFromName    = Setting::get('mail_from_name', '');
        $this->cloudflareToken = Setting::get('cloudflare_token', '');
        $this->mailChannelsApiKey = Setting::get('mailchannels_api_key', '');
        // Load password into state so it's preserved on save even if field is left blank
        $this->mailPassword = Setting::get('mail_password', '');
    }

    public function save(): void
    {
        $this->validate([
            'mailFromAddress' => ['required', 'email'],
            'mailFromName'    => ['required', 'string', 'max:255'],
        ]);

        $cfg = $this->resolvedMailer();

        Setting::setMany([
            'mail_mailer'          => $cfg['mailer'],
            'mail_host'            => $cfg['host'],
            'mail_port'            => $cfg['port'],
            'mail_username'        => $cfg['username'],
            'mail_password'        => $cfg['password'],
            'mail_encryption'      => $cfg['encryption'],
            'mail_from_address'    => $this->mailFromAddress,
            'mail_from_name'       => $this->mailFromName,
            'cloudflare_token'     => $this->cloudflareToken,
            'mailchannels_api_key' => $cfg['mailchannels_key'],
        ]);

        // Apply to running config
        config([
            'mail.default'                 => $cfg['mailer'],
            'mail.mailers.smtp.host'       => $cfg['host'],
            'mail.mailers.smtp.port'       => $cfg['port'],
            'mail.mailers.smtp.username'   => $cfg['username'],
            'mail.mailers.smtp.password'   => $cfg['password'],
            'mail.mailers.smtp.encryption' => $cfg['encryption'],
            'mail.from.address'            => $this->mailFromAddress,
            'mail.from.name'               => $this->mailFromName,
            'services.cloudflare.api_token' => $this->cloudflareToken,
            'services.mailchannels.api_key' => $cfg['mailchannels_key'],
        ]);

        $this->saved = true;
    }

    public function updatedMailMode(): void
    {
        $this->saved = false;
    }

    private function resolvedMailer(): array
    {
        return match ($this->mailMode) {
            'postfix' => [
                'mailer'           => 'smtp',
                'host'             => $this->postfixHost,
                'port'             => $this->postfixPort,
                'username'         => '',
                'password'         => '',
                'encryption'       => '',
                'mailchannels_key' => '',
            ],
            'mailchannels' => [
                'mailer'           => 'mailchannels',
                'host'             => '',
                'port'             => '',
                'username'         => '',
                'password'         => '',
                'encryption'       => '',
                'mailchannels_key' => $this->mailChannelsApiKey,
            ],
            'log' => [
                'mailer'           => 'log',
                'host'             => '',
                'port'             => '',
                'username'         => '',
                'password'         => '',
                'encryption'       => '',
                'mailchannels_key' => '',
            ],
            default => [ // smtp
                'mailer'           => 'smtp',
                'host'             => $this->mailHost,
                'port'             => $this->mailPort,
                'username'         => $this->mailUsername,
                'password'         => $this->mailPassword,
                'encryption'       => $this->mailEncryption,
                'mailchannels_key' => '',
            ],
        };
    }

    public function render(): View
    {
        return view('livewire.settings.general-settings');
    }
}
