<x-admin-layout title="AI Studio">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">AI Writing Studio</h1>
            <p class="mt-1 text-sm text-slate-500">Drafts never go live on their own. Approve, schedule, edit or reject each one.</p>
        </div>
        <div class="text-right text-sm text-slate-500">
            AI spend this month: <strong class="text-slate-800">${{ number_format($spend, 4) }}</strong>
            @if ($budget > 0) of ${{ number_format($budget, 2) }} budget @endif
        </div>
    </div>

    @unless ($keySet)
        <div class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
            No AI key is set on the server (<code>{{ strtoupper(config('ai.provider')) }}_API_KEY</code> in <code>.env</code>), and no key is added in <a href="{{ route('admin.automation.settings') }}" class="underline">Settings &amp; API</a>, so generation will fail until one is added.
        </div>
    @endunless

    <form method="POST" action="{{ route('admin.ai-studio.generate') }}" class="mt-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4">
        @csrf
        <div>
            <label class="block text-xs font-medium text-slate-500">Write drafts for</label>
            <select name="type" class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach ($templates as $key => $t)
                    <option value="{{ $key }}" @selected($type === $key)>{{ $t->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500">How many</label>
            <select name="count" class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach ([1, 2, 3] as $n)<option>{{ $n }}</option>@endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700" onclick="this.disabled=true;this.innerText='Writing…';this.form.submit()">Generate</button>
        <p class="text-xs text-slate-400">Picks the emptiest topics automatically. Takes ~10 s per draft.</p>
    </form>

    <div class="mt-6 flex flex-wrap gap-2 text-sm">
        @foreach (['generated' => 'Waiting', 'approved' => 'Approved', 'rejected' => 'Rejected', 'failed' => 'Failed', 'all' => 'All'] as $s => $label)
            <a href="{{ route('admin.ai-studio.index', ['status' => $s, 'type' => $type]) }}"
               class="rounded-full border px-3 py-1 {{ $status === $s ? 'border-violet-600 bg-violet-600 text-white' : 'border-slate-300 text-slate-600' }}">
                {{ $label }} @if ($s !== 'all')({{ $counts[$s] ?? 0 }})@endif
            </a>
        @endforeach
    </div>

    <table class="mt-4 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr><th class="px-4 py-2">Draft</th><th class="px-4 py-2">Type</th><th class="px-4 py-2">Score</th><th class="px-4 py-2">Status</th><th class="px-4 py-2">Written</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($drafts as $d)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2">
                        <a href="{{ route('admin.ai-studio.show', $d) }}" class="text-violet-700 hover:underline">
                            {{ Str::limit($d->payload['title'] ?? $d->payload['name_en'] ?? $d->payload['body'] ?? $d->error ?? '—', 80) }}
                        </a>
                        <div class="text-xs text-slate-400">{{ collect($d->params)->map(fn ($v, $k) => is_array($v) ? null : "$k: $v")->filter()->implode(' · ') }}</div>
                    </td>
                    <td class="px-4 py-2 text-slate-500">{{ $d->content_type }}</td>
                    <td class="px-4 py-2">
                        @if ($d->quality_score !== null)
                            <span class="rounded-full px-2 py-0.5 text-xs {{ $d->hasBlockingFailure() ? 'bg-red-100 text-red-700' : ($d->quality_score >= 80 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') }}">{{ $d->quality_score }}{{ $d->hasBlockingFailure() ? ' · blocked' : '' }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-slate-500">{{ $d->status }}</td>
                    <td class="px-4 py-2 text-xs text-slate-400">{{ $d->created_at->format('m-d-Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Nothing here.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $drafts->links() }}</div>
</x-admin-layout>
