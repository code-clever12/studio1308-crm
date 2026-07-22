<?php

use App\Jobs\ExpireWaitlistEntries;
use App\Jobs\MarkNoShowAppointments;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new MarkNoShowAppointments)->everyFiveMinutes();

Schedule::job(new ExpireWaitlistEntries)->hourly();

Schedule::command('backup:database')->dailyAt('02:00')->onOneServer();
