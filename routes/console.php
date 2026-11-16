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

// Automation engine (docs/AUTOMATION.md). Jobs, times and on/off live in the
// automation_jobs table and are changed in Admin → Automation, not here.
Artisan::command('automation:run-due', function (\App\Services\Automation\AutomationRunner $runner) {
    foreach ($runner->runDue() as $run) {
        $this->line("{$run->job_key}: {$run->status} — {$run->message}");
    }
})->purpose('Run every automation job that is due');

Artisan::command('automation:run {key}', function (\App\Services\Automation\AutomationRunner $runner, string $key) {
    $job = \App\Models\AutomationJob::where('key', $key)->firstOrFail();
    $run = $runner->run($job, 'console');
    $this->line($run ? "{$run->status}: {$run->message}" : 'Already running (locked), skipped.');
})->purpose('Run one automation job now');

Schedule::command('automation:run-due')->everyFiveMinutes()->withoutOverlapping(15);
