<?php

namespace App\Services\Automation;

use App\Models\AutomationJob;
use App\Models\AutomationRun;
use App\Models\Setting;
use App\Services\Automation\Tasks;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AutomationRunner
{
    public const TASKS = [
        'publish_scheduled' => Tasks\PublishScheduledTask::class,
        'generate_drafts' => Tasks\GenerateDraftsTask::class,
        'health_report' => Tasks\HealthReportTask::class,
        'broken_links' => Tasks\BrokenLinksTask::class,
        'content_review' => Tasks\ContentReviewTask::class,
        'social_publish' => Tasks\SocialPublishTask::class,
        'import_trivia' => Tasks\ImportTriviaTask::class,
        'build_quizzes' => Tasks\BuildQuizzesTask::class,
        'import_quotes' => Tasks\ImportQuotesTask::class,
        'daily_picks' => Tasks\DailyPicksTask::class,
        'import_wallpapers' => Tasks\ImportWallpapersTask::class,
    ];

    /** Called by cron every 5 minutes. Returns the runs it made. */
    public function runDue(): array
    {
        $now = now();
        $runs = [];
        foreach (AutomationJob::where('enabled', true)->get() as $job) {
            if ($job->next_run_at === null) {
                // First sight of a job: schedule it, don't fire immediately on deploy.
                $job->update(['next_run_at' => $job->nextSlotAfter($now)]);
                continue;
            }
            if ($job->isDue($now)) {
                $runs[] = $this->run($job, 'cron');
            }
        }

        return array_filter($runs);
    }

    /**
     * Runs one job under a lock. A second caller while it runs gets null (no duplicate run).
     */
    public function run(AutomationJob $job, string $triggeredBy): ?AutomationRun
    {
        $lock = Cache::lock('automation:'.$job->key, 900);
        if (! $lock->get()) {
            return null;
        }

        try {
            $run = AutomationRun::create(['job_key' => $job->key, 'triggered_by' => $triggeredBy, 'started_at' => now(), 'status' => 'running']);
            $task = app(self::TASKS[$job->key] ?? throw new \RuntimeException("No task class for {$job->key}"));

            try {
                $message = $task->handle($run);
                $run->fill(['status' => $message === null ? 'skipped' : 'success', 'message' => $message ?? $run->message ?? 'Nothing to do.']);
                $job->fill(['consecutive_failures' => 0, 'next_run_at' => $job->nextSlotAfter(now())]);
            } catch (Throwable $e) {
                Log::error("Automation job {$job->key} failed", ['error' => $e->getMessage()]);
                $run->fill(['status' => 'failed', 'message' => mb_substr($e->getMessage(), 0, 1000)]);
                $failures = $job->consecutive_failures + 1;
                // Retry with backoff (10, 20, 40 min) up to max_retries, then wait for the normal slot and alert.
                $retry = $failures <= $job->max_retries;
                $job->fill([
                    'consecutive_failures' => $failures,
                    'next_run_at' => $retry ? now()->addMinutes(10 * 2 ** ($failures - 1)) : $job->nextSlotAfter(now()),
                ]);
                if (! $retry) {
                    $this->alert($job, $run);
                }
            }

            $run->finished_at = now();
            $run->save();
            $job->fill(['last_run_at' => $run->started_at, 'last_status' => $run->status])->save();

            return $run;
        } finally {
            $lock->release();
        }
    }

    private function alert(AutomationJob $job, AutomationRun $run): void
    {
        $to = trim((string) Setting::get('alert_email'));
        Log::critical("Automation job {$job->key} failed {$job->consecutive_failures} times in a row", ['run' => $run->id]);
        if ($to === '' || config('mail.default') === 'log') {
            return;
        }
        try {
            Mail::raw("BuntyFun automation job \"{$job->name}\" failed {$job->consecutive_failures} times in a row.\n\nLast error: {$run->message}\n\nOpen Admin → Automation to retry.", function ($m) use ($to, $job) {
                $m->to($to)->subject("[BuntyFun] Automation failing: {$job->name}");
            });
        } catch (Throwable $e) {
            Log::error('Automation alert mail failed', ['error' => $e->getMessage()]);
        }
    }
}
