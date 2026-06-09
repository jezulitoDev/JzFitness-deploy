<?php

use App\Jobs\RefreshStravaTokensJob;
use App\Jobs\SyncStravaActivitiesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SyncStravaActivitiesJob)->hourly();
Schedule::job(new RefreshStravaTokensJob)->everyFourHours();
