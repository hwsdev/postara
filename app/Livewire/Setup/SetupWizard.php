<?php

namespace App\Livewire\Setup;

use App\Models\Setting;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Livewire\Component;

class SetupWizard extends Component
{
    public int $step = 1;
    public int $totalSteps = 3;

    // Step 1 — App
    public string $appName = 'Postara';
    public string $appUrl = '';
    public string $appTimezone = 'UTC';

    // Step 2 — Mail mode
    // 'postfix'      = self-hosted Postfix on same server (no auth, port 25)
    // 'smtp'         = any external SMTP relay (Brevo, Mailgun, SES, etc.)
    // 'mailchannels' = MailChannels Email API (HTTP, free 100/day)
    // 'log'          = dev/testing only, writes to laravel.log
    public string $mailMode = 'postfix';

    // SMTP relay fields (only used when mailMode = smtp)
    public string $mailHost = '';
    public string $mailPort = '587';
    public string $mailUsername = '';
    public string $mailPassword = '';
    public string $mailEncryption = 'tls';

    // Postfix fields (only used when mailMode = postfix)
    public string $postfixHost = '127.0.0.1';
    public string $postfixPort = '25';

    // MailChannels fields
    public string $mailChannelsApiKey = '';

    // Shared
    public string $mailFromAddress = '';
    public string $mailFromName = '';
    public ?string $mailTestResult = null;

    // Step 3 — Account
    public string $userName = '';
    public string $userEmail = '';
    public string $userPassword = '';
    public string $userPasswordConfirmation = '';
    public string $workspaceName = '';

    // Cloudflare (optional)
    public string $cloudflareToken = '';

    // Derived: actual mailer config to save
    private function resolvedMailer(): array
    {
        return match ($this->mailMode) {
            'postfix' => [
                'mailer'          => 'smtp',
                'host'            => $this->postfixHost,
                'port'            => $this->postfixPort,
                'username'        => '',
                'password'        => '',
                'encryption'      => '',
                'mailchannels_key' => '',
            ],
            'smtp' => [
                'mailer'          => 'smtp',
                'host'            => $this->mailHost,
                'port'            => $this->mailPort,
                'username'        => $this->mailUsername,
                'password'        => $this->mailPassword,
                'encryption'      => $this->mailEncryption,
                'mailchannels_key' => '',
            ],
            'mailchannels' => [
                'mailer'          => 'mailchannels',
                'host'            => '',
                'port'            => '',
                'username'        => '',
                'password'        => '',
                'encryption'      => '',
                'mailchannels_key' => $this->mailChannelsApiKey,
            ],
            default => [ // log
                'mailer'          => 'log',
                'host'            => '',
                'port'            => '',
                'username'        => '',
                'password'        => '',
                'encryption'      => '',
                'mailchannels_key' => '',
            ],
        };
    }

    public function mount(): void
    {
        if (Setting::isSetupComplete()) {
            $this->redirect(route('dashboard'));
        }

        $this->appUrl = config('app.url', 'http://localhost');
        $this->mailFromName = 'Postara';
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        $this->step++;
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
        $this->mailTestResult = null;
    }

    public function updatedMailMode(): void
    {
        $this->mailTestResult = null;
    }

    public function testMailConnection(): void
    {
        $this->mailTestResult = null;

        $cfg = $this->resolvedMailer();

        if ($cfg['mailer'] === 'log') {
            $this->mailTestResult = 'success';
            return;
        }

        // MailChannels: test by hitting the API with a dry-run (no actual send)
        if ($cfg['mailer'] === 'mailchannels') {
            if (empty($this->mailChannelsApiKey)) {
                $this->mailTestResult = 'error:API key is required.';
                return;
            }
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'X-Api-Key' => $this->mailChannelsApiKey,
                ])->get('https://api.mailchannels.net/tx/v1/account');

                $this->mailTestResult = $response->successful() ? 'success' : 'error:Invalid API key (status '.$response->status().')';
            } catch (\Exception $e) {
                $this->mailTestResult = 'error:'.$e->getMessage();
            }
            return;
        }

        // SMTP / Postfix
        try {
            // $tls = true  → implicit SSL (port 465)
            // $tls = false → plain + STARTTLS negotiation (port 587/25)
            // $tls = null  → auto-detect by port
            $tls = match ($cfg['encryption']) {
                'ssl'  => true,
                'tls'  => false,
                default => null,
            };

            $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                $cfg['host'],
                (int) $cfg['port'],
                $tls
            );

            if (! empty($cfg['username'])) {
                $transport->setUsername($cfg['username']);
                $transport->setPassword($cfg['password']);
            }

            $transport->start();
            $this->mailTestResult = 'success';
        } catch (\Exception $e) {
            $this->mailTestResult = 'error:' . $e->getMessage();
        }
    }

    public function finish(): void
    {
        $this->validateCurrentStep();

        $cfg = $this->resolvedMailer();

        Setting::setMany([
            'app_name'             => $this->appName,
            'app_url'              => $this->appUrl,
            'app_timezone'         => $this->appTimezone,
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
            'setup_completed'      => '1',
        ]);

        config([
            'app.name'                      => $this->appName,
            'app.url'                       => $this->appUrl,
            'app.timezone'                  => $this->appTimezone,
            'mail.default'                  => $cfg['mailer'],
            'mail.mailers.smtp.host'        => $cfg['host'],
            'mail.mailers.smtp.port'        => $cfg['port'],
            'mail.mailers.smtp.username'    => $cfg['username'],
            'mail.mailers.smtp.password'    => $cfg['password'],
            'mail.mailers.smtp.encryption'  => $cfg['encryption'],
            'mail.from.address'             => $this->mailFromAddress,
            'mail.from.name'                => $this->mailFromName,
            'services.cloudflare.api_token' => $this->cloudflareToken,
            'services.mailchannels.api_key' => $cfg['mailchannels_key'],
        ]);

        $user = User::create([
            'name'     => $this->userName,
            'email'    => $this->userEmail,
            'password' => Hash::make($this->userPassword),
        ]);

        $workspace = Workspace::create(['name' => $this->workspaceName]);
        $workspace->users()->attach($user->id, ['role' => 'owner']);

        Auth::login($user);
        session(['current_workspace_id' => $workspace->id]);

        $this->redirect(route('dashboard'));
    }

    private function validateCurrentStep(): void
    {
        match ($this->step) {
            1 => $this->validate([
                'appName'     => ['required', 'string', 'max:100'],
                'appUrl'      => ['required', 'url'],
                'appTimezone' => ['required', 'string'],
            ]),

            2 => $this->validate(
                array_merge(
                    [
                        'mailFromAddress' => ['required', 'email'],
                        'mailFromName'    => ['required', 'string'],
                    ],
                    $this->mailMode === 'smtp' ? [
                        'mailHost' => ['required', 'string'],
                        'mailPort' => ['required', 'numeric', 'between:1,65535'],
                    ] : [],
                    $this->mailMode === 'postfix' ? [
                        'postfixHost' => ['required', 'string'],
                        'postfixPort' => ['required', 'numeric', 'between:1,65535'],
                    ] : [],
                    $this->mailMode === 'mailchannels' ? [
                        'mailChannelsApiKey' => ['required', 'string'],
                    ] : [],
                ),
                [
                    'mailFromAddress.required'    => 'From address is required.',
                    'mailFromAddress.email'        => 'Must be a valid email.',
                    'mailHost.required'            => 'SMTP host is required.',
                    'postfixHost.required'         => 'Postfix host is required.',
                    'mailChannelsApiKey.required'  => 'MailChannels API key is required.',
                ]
            ),

            3 => $this->validate([
                'userName'                 => ['required', 'string', 'max:255'],
                'userEmail'                => ['required', 'email', 'unique:users,email'],
                'userPassword'             => ['required', 'min:8', 'same:userPasswordConfirmation'],
                'userPasswordConfirmation' => ['required'],
                'workspaceName'            => ['required', 'string', 'max:255'],
            ]),

            default => null,
        };
    }

    public function render(): View
    {
        return view('livewire.setup.setup-wizard');
    }
}
