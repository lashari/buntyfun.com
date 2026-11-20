<x-admin-layout title="Automation">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Automation Control Center</h1>
        <div class="flex gap-2 text-sm">
            <a href="{{ route('admin.automation.calendar') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 hover:bg-slate-50">Publishing calendar</a>
            <a href="{{ route('admin.automation.settings') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 hover:bg-slate-50">Settings &amp; API</a>
        </div>
    </div>

    @unless ($cronAlive)
        <div class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
            No automation activity in the last 20 minutes: the server cron is probably not set. cPanel → Cron Jobs, every minute:
            <code class="block mt-1">/usr/local/bin/ea-php84 {{ base_path('artisan') }} schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code>
        </div>
    @endunless

    <div class="mt-6 grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ([
            'Drafts waiting' => $stats['drafts_waiting'],
            'Drafts (30 d)' => $stats['drafts_30d'],
            'Approved (30 d)' => $stats['approved_30d'],
            'Auto-published (30 d)' => $stats['auto_published_30d'],
            'Scheduled ahead' => $stats['scheduled'],
            'AI tokens (30 d)' => number_format($stats['tokens_30d']),
        ] as $label => $value)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs text-slate-500">{{ $label }}</p>
                <p class="mt-1 text-xl font-bold">{{ $value }}</p>
            </div>
        @endforeach
    </div>
    <p class="mt-2 text-xs text-slate-500">
        Estimated AI cost: ${{ number_format($stats['cost_month'], 4) }} this month{{ $stats['budget'] > 0 ? ' of $'.number_format($stats['budget'], 2).' budget' : '' }}, ${{ number_format($stats['cost_30d'], 4) }} last 30 days (token counts from the provider × your price settings).
    </p>

    <h2 class="mt-8 text-lg font-bold">Jobs <span class="text-xs font-normal text-slate-500">· times are server time ({{ config('app.timezone') }}, now {{ now()->format('H:i') }})</span></h2>
    <div class="mt-3 space-y-3">
        @foreach ($jobs as $job)
            <form method="POST" action="{{ route('admin.automation.update', $job) }}" class="rounded-xl border border-slate-200 bg-white p-4">
                @csrf @method('PUT')
                <div class="space-y-3">
                    <div>
                        <p class="font-semibold">{{ $job->name }}
                            @if ($job->last_status)
                                <span class="ml-2 rounded-full px-2 py-0.5 text-xs {{ ['success' => 'bg-emerald-100 text-emerald-700', 'failed' => 'bg-red-100 text-red-700', 'skipped' => 'bg-slate-100 text-slate-600', 'running' => 'bg-sky-100 text-sky-700'][$job->last_status] ?? '' }}">{{ $job->last_status }}</span>
                            @endif
                            @if ($job->consecutive_failures)<span class="ml-1 text-xs text-red-600">{{ $job->consecutive_failures }} failure(s) in a row</span>@endif
                        </p>
                        <p class="text-xs text-slate-500">{{ $job->description }}</p>
                        <p class="mt-1 text-xs text-slate-500">
                            Last: {{ $job->last_run_at?->format('m-d-Y H:i') ?? 'never' }} ·
                            Next: {{ $job->enabled ? ($job->next_run_at?->format('m-d-Y H:i') ?? 'at first cron run') : 'disabled' }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <label class="flex items-center gap-1"><input type="checkbox" name="enabled" value="1" @checked($job->enabled)> On</label>
                        <select name="frequency" class="rounded border border-slate-300 px-2 py-1">
                            @foreach (['every_5_minutes' => 'Every 5 min', 'hourly' => 'Hourly', 'daily' => 'Daily', 'weekly' => 'Weekly (Mon)'] as $v => $l)
                                <option value="{{ $v }}" @selected($job->frequency === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                        <input type="time" name="run_at" value="{{ $job->run_at }}" class="rounded border border-slate-300 px-2 py-1" title="Time (daily/weekly)">
                        <label class="text-xs text-slate-500">retries <input type="number" name="max_retries" min="0" max="10" value="{{ $job->max_retries }}" class="w-14 rounded border border-slate-300 px-1 py-1"></label>
                        <button class="rounded bg-brand-600 px-3 py-1 font-semibold text-white">Save</button>
                        <button type="submit" form="run-{{ $job->key }}" class="rounded border border-slate-300 px-3 py-1 hover:bg-slate-50">Run now</button>
                    </div>
                </div>
            </form>
            <form id="run-{{ $job->key }}" method="POST" action="{{ route('admin.automation.run', $job) }}" class="hidden">@csrf</form>
        @endforeach
    </div>

    @if ($failures->isNotEmpty())
        <h2 class="mt-8 text-lg font-bold text-red-700">Errors (7 days)</h2>
        <ul class="mt-2 space-y-1 text-sm">
            @foreach ($failures as $f)
                <li><a href="{{ route('admin.automation.runs.show', $f) }}" class="text-red-700 hover:underline">{{ $f->started_at->format('m-d-Y H:i') }} {{ $f->job_key }}</a> — {{ Str::limit($f->message, 160) }}</li>
            @endforeach
        </ul>
    @endif

    <h2 class="mt-8 text-lg font-bold">Run history</h2>
    <table class="mt-3 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr><th class="px-3 py-2">Started</th><th class="px-3 py-2">Job</th><th class="px-3 py-2">By</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Items</th><th class="px-3 py-2">Message</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($runs as $r)
                <tr>
                    <td class="px-3 py-2 text-xs"><a href="{{ route('admin.automation.runs.show', $r) }}" class="text-navy-600 hover:underline">{{ $r->started_at->format('m-d-Y H:i') }}</a></td>
                    <td class="px-3 py-2">{{ $r->job_key }}</td>
                    <td class="px-3 py-2 text-xs text-slate-500">{{ $r->triggered_by }}</td>
                    <td class="px-3 py-2 {{ $r->status === 'failed' ? 'text-red-700' : '' }}">{{ $r->status }}</td>
                    <td class="px-3 py-2">{{ $r->items }}</td>
                    <td class="px-3 py-2 text-xs text-slate-600">{{ Str::limit($r->message, 120) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">No runs yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-admin-layout>
