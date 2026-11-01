<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// The architecture decision (docs/ARCHITECTURE.md) is a database-driven queue with
// no persistent worker — this is what actually makes that true. Nothing dispatches
// a queued job yet, so this is currently a no-op every minute; it's here so the
// cron entry in docs/DEPLOY.md does something real the moment anything does queue
// a job, instead of silently doing nothing forever.
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();
