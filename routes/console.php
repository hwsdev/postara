<?php

use App\Jobs\ProcessCampaignJob;
use App\Models\Campaign;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Dispatch scheduled campaigns that are due
Schedule::call(function () {
    Campaign::where('status', 'scheduled')
        ->where('scheduled_at', '<=', now())
        ->each(fn (Campaign $campaign) => ProcessCampaignJob::dispatch($campaign));
})->everyMinute()->name('process-scheduled-campaigns')->withoutOverlapping();
