<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadSettingsFromDatabase();
        $this->registerMailChannelsTransport();
    }

    /**
     * Register MailChannels as a custom Symfony Mailer transport.
     * Usage: set MAIL_MAILER=mailchannels in config.
     */
    private function registerMailChannelsTransport(): void
    {
        \Illuminate\Support\Facades\Mail::extend('mailchannels', function () {
            return new \App\Services\MailChannelsTransport(
                config('services.mailchannels.api_key', '')
            );
        });
    }

    /**
     * Apply settings stored in the DB to runtime config.
     * This runs on every request so mail/app config is always up-to-date
     * without needing to touch .env after the setup wizard completes.
     */
    private function loadSettingsFromDatabase(): void
    {
        try {
            // Only run if the settings table exists (i.e. migrations have run)
            if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return;
            }

            $map = [
                'app_name'             => 'app.name',
                'app_url'              => 'app.url',
                'app_timezone'         => 'app.timezone',
                'mail_mailer'          => 'mail.default',
                'mail_host'            => 'mail.mailers.smtp.host',
                'mail_port'            => 'mail.mailers.smtp.port',
                'mail_username'        => 'mail.mailers.smtp.username',
                'mail_password'        => 'mail.mailers.smtp.password',
                'mail_encryption'      => 'mail.mailers.smtp.encryption',
                'mail_from_address'    => 'mail.from.address',
                'mail_from_name'       => 'mail.from.name',
                'cloudflare_token'     => 'services.cloudflare.api_token',
                'mailchannels_api_key' => 'services.mailchannels.api_key',
            ];

            // Fetch all relevant settings in one query
            $settings = Setting::whereIn('key', array_keys($map))->pluck('value', 'key');

            foreach ($map as $settingKey => $configKey) {
                if ($settings->has($settingKey) && $settings[$settingKey] !== null) {
                    config([$configKey => $settings[$settingKey]]);
                }
            }
        } catch (\Exception) {
            // Silently fail — DB might not be ready on first boot
        }
    }
}
