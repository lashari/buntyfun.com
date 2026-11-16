<?php

namespace App\Services\Automation\Tasks;

use App\Models\AiDraft;
use App\Models\AutomationRun;
use App\Services\Automation\AutomationTask;
use App\Services\Content\DraftGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HealthReportTask implements AutomationTask
{
    private const TABLES = ['birthday_messages', 'messages', 'urdu_contents', 'name_entries', 'quizzes', 'games', 'wallpapers', 'ai_tools'];

    public function handle(AutomationRun $run): ?string
    {
        $content = [];
        foreach (self::TABLES as $t) {
            if (! Schema::hasTable($t)) continue;
            $content[$t] = DB::table($t)->selectRaw("status, count(*) as c")->groupBy('status')->pluck('c', 'status')->all();
        }

        $failedRuns = AutomationRun::where('status', 'failed')->where('started_at', '>=', now()->subDay())->count();
        $details = [
            'content' => $content,
            'drafts_waiting' => AiDraft::where('status', 'generated')->count(),
            'drafts_failed_24h' => AiDraft::where('status', 'failed')->where('created_at', '>=', now()->subDay())->count(),
            'automation_failed_24h' => $failedRuns,
            'queue_pending' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : null,
            'queue_failed' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : null,
            'ai_spend_month_usd' => round(DraftGenerator::monthSpend(), 4),
            'ai_key_configured' => (bool) (config('ai.provider') === 'openai' ? config('ai.openai.key') : config('ai.gemini.key'))
                || \App\Models\AiApiKey::where('enabled', true)->exists(),
            'disk_free_mb' => @disk_free_space(storage_path()) !== false ? (int) (disk_free_space(storage_path()) / 1048576) : null,
        ];
        $run->details = $details;

        $problems = array_filter([
            $details['ai_key_configured'] ? null : 'AI key not set',
            $failedRuns ? "{$failedRuns} failed automation run(s)" : null,
            $details['queue_failed'] ? "{$details['queue_failed']} failed queue job(s)" : null,
        ]);

        return $problems ? 'Attention: '.implode('; ', $problems).'.' : 'All healthy. '.$details['drafts_waiting'].' draft(s) waiting for review.';
    }
}
