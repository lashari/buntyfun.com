<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiDraft;
use App\Models\AuditLog;
use App\Models\AutomationJob;
use App\Models\AutomationRun;
use App\Models\ContentMeta;
use App\Models\Setting;
use App\Services\Automation\AutomationRunner;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    public function index()
    {
        $since = now()->subDays(30);

        return view('admin.automation.index', [
            'jobs' => AutomationJob::orderBy('id')->get(),
            'runs' => AutomationRun::latest('started_at')->limit(30)->get(),
            'failures' => AutomationRun::where('status', 'failed')->where('started_at', '>=', now()->subDays(7))->latest('started_at')->limit(10)->get(),
            'stats' => [
                'drafts_waiting' => AiDraft::where('status', 'generated')->count(),
                'drafts_30d' => AiDraft::where('created_at', '>=', $since)->count(),
                'approved_30d' => AiDraft::where('status', 'approved')->where('reviewed_at', '>=', $since)->count(),
                'auto_published_30d' => AuditLog::where('action', 'auto:published_scheduled')->where('created_at', '>=', $since)->count(),
                'scheduled' => ContentMeta::where('publish_at', '>', now())->count(),
                'tokens_30d' => (int) AiDraft::where('created_at', '>=', $since)->sum('input_tokens') + (int) AiDraft::where('created_at', '>=', $since)->sum('output_tokens'),
                'cost_30d' => (float) AiDraft::where('created_at', '>=', $since)->sum('cost_usd'),
                'cost_month' => (float) AiDraft::where('created_at', '>=', now()->startOfMonth())->sum('cost_usd'),
                'budget' => (float) Setting::get('ai_monthly_budget_usd'),
            ],
            // Cron health: publish_scheduled runs every 5 min; no run for 20 min = cron is not running.
            'cronAlive' => AutomationRun::where('started_at', '>=', now()->subMinutes(20))->exists()
                || AutomationJob::where('key', 'publish_scheduled')->whereNotNull('next_run_at')->where('updated_at', '>=', now()->subMinutes(20))->exists(),
        ]);
    }

    public function update(Request $request, AutomationJob $job)
    {
        abort_unless($request->user()->canPublish(), 403);
        $data = $request->validate([
            'frequency' => ['required', 'in:every_5_minutes,hourly,daily,weekly'],
            'run_at' => ['nullable', 'date_format:H:i'],
            'max_retries' => ['required', 'integer', 'min:0', 'max:10'],
        ]);
        $job->fill($data + ['enabled' => $request->boolean('enabled')]);
        $job->next_run_at = $job->enabled ? $job->nextSlotAfter(now()) : null;
        $job->consecutive_failures = 0;
        $job->save();
        AuditLog::record($request->user(), 'automation_job_updated', 'automation_job', $job->id);

        return back()->with('status', "Saved \"{$job->name}\".");
    }

    public function run(Request $request, AutomationJob $job, AutomationRunner $runner)
    {
        abort_unless($request->user()->canPublish(), 403);
        $run = $runner->run($job, 'user:'.$request->user()->id);

        return redirect()->route('admin.automation.index')->with('status', $run
            ? "{$job->name}: {$run->status} — {$run->message}"
            : "{$job->name} is already running; not started twice.");
    }

    public function showRun(AutomationRun $run)
    {
        return view('admin.automation.run', ['run' => $run]);
    }

    public function settings()
    {
        return view('admin.automation.settings', [
            'values' => collect(Setting::DEFAULTS)->map(fn ($v, $k) => Setting::get($k)),
            'keys' => \App\Models\AiApiKey::orderBy('provider')->orderBy('priority')->orderBy('id')->get(),
        ]);
    }

    public function saveSettings(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        $data = $request->validate([
            'drafts_per_day' => ['required', 'integer', 'min:0', 'max:30'],
            'type_weights' => ['required', 'array'],
            'type_weights.*' => ['required', 'integer', 'min:0', 'max:10'],
            'ai_monthly_budget_usd' => ['required', 'numeric', 'min:0', 'max:500'],
            'ai_price_input_per_m' => ['required', 'numeric', 'min:0', 'max:100'],
            'ai_price_output_per_m' => ['required', 'numeric', 'min:0', 'max:100'],
            'approved_name_references' => ['nullable', 'string', 'max:3000'],
            'alert_email' => ['nullable', 'email'],
        ]);

        Setting::put('drafts_per_day', (int) $data['drafts_per_day']);
        Setting::put('type_weights', array_map('intval', array_intersect_key($data['type_weights'], Setting::DEFAULTS['type_weights'])));
        Setting::put('ai_monthly_budget_usd', (float) $data['ai_monthly_budget_usd']);
        Setting::put('ai_price_input_per_m', (float) $data['ai_price_input_per_m']);
        Setting::put('ai_price_output_per_m', (float) $data['ai_price_output_per_m']);
        Setting::put('approved_name_references', collect(preg_split('/\R/', (string) ($data['approved_name_references'] ?? '')))->map('trim')->filter()->values()->all());
        Setting::put('alert_email', (string) ($data['alert_email'] ?? ''));
        AuditLog::record($request->user(), 'settings_updated', 'settings', 0);

        return back()->with('status', 'Settings saved.');
    }

    public function calendar()
    {
        $scheduled = ContentMeta::where('publish_at', '>', now()->subDays(1))->with('metable')->orderBy('publish_at')->get()
            ->filter(fn ($m) => $m->metable && $m->metable->status !== 'published');
        $published = AuditLog::whereIn('action', ['published', 'auto:published_scheduled', 'approved_ai_draft:now'])
            ->where('created_at', '>=', now()->subDays(14))->latest()->get();

        return view('admin.automation.calendar', [
            'scheduled' => $scheduled->groupBy(fn ($m) => $m->publish_at->format('Y-m-d')),
            'published' => $published->groupBy(fn ($a) => $a->created_at->format('Y-m-d')),
        ]);
    }
}
