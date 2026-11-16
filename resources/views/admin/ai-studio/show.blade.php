@php
    $p = $draft->payload ?? [];
    $editable = $draft->status === 'generated';
    $rtl = ($p['language'] ?? null) === 'ur';
@endphp
<x-admin-layout title="Draft #{{ $draft->id }}">
    <a href="{{ route('admin.ai-studio.index') }}" class="text-sm text-slate-500 hover:underline">← AI Studio</a>
    <div class="mt-2 flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-bold">{{ $template->label() }} draft #{{ $draft->id }}</h1>
        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ $draft->status }}</span>
        @if ($draft->quality_score !== null)<span class="rounded-full bg-violet-100 px-2 py-0.5 text-xs text-violet-700">score {{ $draft->quality_score }}</span>@endif
        @if ($draft->target_id)<span class="text-xs text-slate-500">→ {{ $draft->target_type }} #{{ $draft->target_id }}</span>@endif
    </div>

    @if ($draft->status === 'failed')
        <div class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">Failed: {{ $draft->error }}</div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('admin.ai-studio.update', $draft) }}" class="space-y-4 lg:col-span-2">
            @csrf @method('PUT')
            @foreach ($template->fields() as $field => $def)
                @php [$label, $kind] = $def; $val = $p[$field] ?? ''; @endphp
                <div>
                    @if ($kind === 'checkbox')
                        <label class="flex items-center gap-2 rounded-lg bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900">
                            <input type="checkbox" name="{{ $field }}" value="1" @checked($val) @disabled(! $editable)> {{ $label }}
                        </label>
                    @else
                        <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
                        @if ($kind === 'select')
                            <select name="{{ $field }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" @disabled(! $editable)>
                                @foreach ($def[2] as $opt)<option value="{{ $opt }}" @selected($val === $opt)>{{ $opt }}</option>@endforeach
                            </select>
                        @elseif ($kind === 'textarea')
                            <textarea name="{{ $field }}" rows="5" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" @if ($rtl && $field === 'body') dir="rtl" @endif @disabled(! $editable)>{{ $val }}</textarea>
                        @else
                            <input type="text" name="{{ $field }}" value="{{ $val }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" @disabled(! $editable)>
                        @endif
                    @endif
                </div>
            @endforeach
            <div>
                <label class="block text-sm font-medium text-slate-700">SEO title</label>
                <input type="text" name="seo_title" maxlength="70" value="{{ $p['seo_title'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" @disabled(! $editable)>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Meta description</label>
                <textarea name="seo_description" rows="2" maxlength="170" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" @disabled(! $editable)>{{ $p['seo_description'] ?? '' }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">FAQ <span class="text-slate-400">(Question || Answer per line)</span></label>
                <textarea name="faq" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs" @disabled(! $editable)>{{ collect($p['faq'] ?? [])->map(fn ($f) => $f['q'].' || '.$f['a'])->implode("\n") }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Social caption</label>
                <textarea name="social_caption" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" @disabled(! $editable)>{{ $p['social_caption'] ?? '' }}</textarea>
                @if (! empty($p['image_brief']))<p class="mt-1 text-xs text-slate-500">Image brief: {{ $p['image_brief'] }}</p>@endif
            </div>
            @if ($editable)
                <button type="submit" class="rounded-lg border border-violet-600 px-4 py-2 text-sm font-semibold text-violet-700 hover:bg-violet-50">Save edits + re-check</button>
            @endif
        </form>

        <div class="space-y-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="text-sm font-semibold">Quality checks</h2>
                <ul class="mt-2 space-y-1 text-sm">
                    @foreach ($draft->checks ?? [] as $c)
                        <li class="{{ $c['ok'] ? 'text-emerald-700' : ($c['blocking'] ? 'text-red-700' : 'text-amber-700') }}">
                            {{ $c['ok'] ? '✓' : ($c['blocking'] ? '✗' : '!') }} {{ $c['label'] }}
                            @if ($c['note'])<span class="text-xs text-slate-500">— {{ $c['note'] }}</span>@endif
                        </li>
                    @endforeach
                </ul>
            </div>

            @if ($editable)
                <div class="rounded-xl border border-slate-200 bg-white p-4 space-y-3">
                    <h2 class="text-sm font-semibold">Decide</h2>
                    @if ($draft->hasBlockingFailure())
                        <p class="text-xs text-red-700">Fix the ✗ checks (edit + save) before approving.</p>
                    @else
                        @if (auth()->user()->canPublish())
                            <form method="POST" action="{{ route('admin.ai-studio.approve', $draft) }}">@csrf<input type="hidden" name="mode" value="now">
                                <button class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Approve + publish now</button></form>
                            <form method="POST" action="{{ route('admin.ai-studio.approve', $draft) }}" class="flex gap-2">@csrf<input type="hidden" name="mode" value="schedule">
                                <input type="datetime-local" name="publish_at" required class="min-w-0 flex-1 rounded-lg border border-slate-300 px-2 py-1 text-sm">
                                <button class="rounded-lg bg-violet-600 px-3 py-1 text-sm font-semibold text-white">Schedule</button></form>
                        @endif
                        <form method="POST" action="{{ route('admin.ai-studio.approve', $draft) }}">@csrf<input type="hidden" name="mode" value="review">
                            <button class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Approve as "in review" (no date)</button></form>
                    @endif
                </div>
            @endif

            @if (in_array($draft->status, ['generated', 'failed'], true))
                <div class="rounded-xl border border-slate-200 bg-white p-4 space-y-3">
                    <form method="POST" action="{{ route('admin.ai-studio.regenerate', $draft) }}">@csrf
                        <button class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Regenerate (same topic)</button></form>
                    <form method="POST" action="{{ route('admin.ai-studio.reject', $draft) }}" class="flex gap-2">@csrf
                        <input type="text" name="note" placeholder="Reason (optional)" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-2 py-1 text-sm">
                        <button class="rounded-lg bg-red-600 px-3 py-1 text-sm font-semibold text-white">Reject</button></form>
                </div>
            @endif

            <details class="rounded-xl border border-slate-200 bg-white p-4 text-xs text-slate-500">
                <summary class="cursor-pointer">Prompt, raw output, cost</summary>
                <p class="mt-2">Tokens in/out: {{ $draft->input_tokens ?? '—' }} / {{ $draft->output_tokens ?? '—' }} · est. ${{ $draft->cost_usd !== null ? number_format($draft->cost_usd, 5) : '—' }}</p>
                <pre class="mt-2 whitespace-pre-wrap">{{ $draft->prompt }}</pre>
                <pre class="mt-2 whitespace-pre-wrap border-t pt-2">{{ $draft->raw_output }}</pre>
            </details>
        </div>
    </div>
</x-admin-layout>
