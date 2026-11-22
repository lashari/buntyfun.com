<x-admin-layout title="Import #{{ $run->id }}">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.import.index') }}" class="text-sm text-slate-500 hover:underline">← Import</a>
            <h1 class="text-2xl font-bold">Import #{{ $run->id }} — {{ $source->label() }}</h1>
            <p class="text-sm text-slate-500">"{{ $run->query }}" → category <b>{{ $run->category }}</b> · target {{ $run->target_count }} · orientation {{ $run->option('orientation') }} · min {{ $run->option('min_width') }}px</p>
        </div>
        @if ($run->status === 'running')
            <form method="POST" action="{{ route('admin.import.stop', $run) }}">@csrf
                <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-50">Stop</button>
            </form>
        @endif
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">{{ session('status') }}</div>
    @endif

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
        <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100">
            <div id="bar" class="h-3 rounded-full bg-brand-600" style="width: {{ $run->target_count ? min(100, round($run->saved * 100 / $run->target_count)) : 0 }}%"></div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3 text-center sm:grid-cols-5">
            <div class="rounded-lg bg-slate-50 p-3"><div class="text-xs text-slate-500">Found</div><div id="c-found" class="text-2xl font-bold">{{ count($run->candidateList()) }}</div></div>
            <div class="rounded-lg bg-slate-50 p-3"><div class="text-xs text-slate-500">Checked</div><div id="c-checked" class="text-2xl font-bold">{{ $run->cursor }}</div></div>
            <div class="rounded-lg bg-emerald-50 p-3"><div class="text-xs text-emerald-700">Saved</div><div id="c-saved" class="text-2xl font-bold text-emerald-700">{{ $run->saved }}</div></div>
            <div class="rounded-lg bg-slate-50 p-3"><div class="text-xs text-slate-500">Skipped</div><div id="c-skipped" class="text-2xl font-bold">{{ $run->skipped }}</div></div>
            <div class="rounded-lg bg-red-50 p-3"><div class="text-xs text-red-600">Failed</div><div id="c-failed" class="text-2xl font-bold text-red-600">{{ $run->failed }}</div></div>
        </div>
        <p id="status-line" class="mt-4 text-sm text-slate-600">
            <b>{{ Str::headline($run->status) }}</b> — {{ $run->message }}
        </p>
        <ul id="live-log" class="mt-2 space-y-1 text-xs text-slate-500"></ul>
    </div>

    @if ($run->status !== 'running')
        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-semibold">Result: {{ $run->saved }} saved, {{ $run->skipped }} skipped, {{ $run->failed }} failed</h2>
            <p class="mt-1 text-sm text-slate-500">Saved items are in <b>Wallpapers</b> as In review ({{ $unpublished }} not published yet). Choose the next step:</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('admin.wallpapers.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Review in Wallpapers</a>
                @if (auth()->user()->canPublish() && $unpublished > 0)
                    <form method="POST" action="{{ route('admin.import.publish', $run) }}">@csrf
                        <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Publish all {{ $unpublished }}</button>
                    </form>
                @endif
                @if ($run->saved > 0)
                    <a href="{{ route('admin.import.zip', $run) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Download ZIP{{ $run->source === 'unsplash' ? ' (CSV, links only)' : ' (images + CSV)' }}</a>
                    <a href="{{ route('admin.import.csv', $run) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Download CSV (Excel)</a>
                @endif
                <form method="POST" action="{{ route('admin.import.store') }}">@csrf
                    <input type="hidden" name="content_type" value="{{ $run->content_type }}">
                    <input type="hidden" name="source" value="{{ $run->source }}">
                    <input type="hidden" name="query" value="{{ $run->query }}">
                    <input type="hidden" name="category" value="{{ $run->category }}">
                    <input type="hidden" name="target_count" value="{{ $run->target_count }}">
                    <input type="hidden" name="orientation" value="{{ $run->option('orientation', 'any') }}">
                    <input type="hidden" name="min_width" value="{{ $run->option('min_width', 1280) }}">
                    <input type="hidden" name="quality_only" value="{{ $run->option('quality_only', true) ? 1 : 0 }}">
                    <input type="hidden" name="page" value="{{ min(100, $run->page + 1) }}">
                    <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Import {{ $run->target_count }} more (next page)</button>
                </form>
                @if (auth()->user()->canPublish() && $unpublished > 0)
                    <form method="POST" action="{{ route('admin.import.undo', $run) }}">@csrf
                        <button class="rounded-lg px-4 py-2 text-sm text-red-600 hover:bg-red-50">Remove the {{ $unpublished }} unpublished from this import</button>
                    </form>
                @endif
            </div>

            @if ($wallpapers->isNotEmpty())
                <div class="mt-6 grid gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($wallpapers as $wallpaper)
                        <a href="{{ route('admin.wallpapers.edit', $wallpaper) }}" class="overflow-hidden rounded-lg border border-slate-200 hover:border-brand-600">
                            <img src="{{ $wallpaper->thumbUrl() }}" alt="{{ $wallpaper->title }}" loading="lazy" class="aspect-video w-full object-cover">
                            <div class="p-2 text-xs">
                                <div class="truncate font-medium">{{ $wallpaper->title }}</div>
                                <div class="text-slate-500">{{ $wallpaper->resolutionLabel() }} · {{ Str::headline($wallpaper->status) }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <h2 class="mt-8 text-lg font-semibold">Item log</h2>
    <div class="mt-2 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-3 py-2">Result</th><th class="px-3 py-2">Title</th><th class="px-3 py-2">Source id</th><th class="px-3 py-2">Note</th></tr></thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2 {{ ['saved' => 'text-emerald-700', 'failed' => 'text-red-600'][$item->status] ?? 'text-slate-500' }}">{{ Str::headline($item->status) }}</td>
                        <td class="px-3 py-2">{{ Str::limit($item->title, 80) }}</td>
                        <td class="px-3 py-2 text-xs text-slate-500">{{ $item->source_key }}</td>
                        <td class="px-3 py-2 text-xs text-slate-500">{{ $item->message }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-4 text-slate-500">Nothing processed yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($run->status === 'running')
        <script>
            (function () {
                const url = @json(route('admin.import.step', $run));
                const token = @json(csrf_token());
                const set = (id, v) => { document.getElementById(id).textContent = v; };
                let errors = 0;

                async function tick() {
                    try {
                        const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const p = await res.json();
                        errors = 0;
                        set('c-found', p.found); set('c-checked', p.checked); set('c-saved', p.saved);
                        set('c-skipped', p.skipped); set('c-failed', p.failed);
                        document.getElementById('bar').style.width = Math.min(100, Math.round(p.saved * 100 / p.target)) + '%';
                        document.getElementById('status-line').textContent = 'Working… ' + (p.message || '');
                        const log = document.getElementById('live-log');
                        log.innerHTML = '';
                        (p.recent || []).forEach(i => {
                            const li = document.createElement('li');
                            li.textContent = (i.status === 'saved' ? '✓ ' : i.status === 'failed' ? '✗ ' : '– ') + (i.title || i.source_key) + (i.message ? ' (' + i.message + ')' : '');
                            log.appendChild(li);
                        });
                        if (p.status === 'running') { setTimeout(tick, 300); } else { location.reload(); }
                    } catch (e) {
                        errors++;
                        document.getElementById('status-line').textContent = 'Connection problem (' + e.message + '), retrying… ' + errors + '/5';
                        if (errors < 5) setTimeout(tick, 3000);
                    }
                }
                tick();
            })();
        </script>
    @endif
</x-admin-layout>
