<x-admin-layout title="Quotes">
    <h1 class="text-2xl font-bold">Quotes</h1>
    <p class="mt-1 text-sm text-slate-600">Imported from QuoteSlate (MIT licence) as In review. Read each quote, fix the author or topic if needed, then publish. Quotes flagged ⚠ have an author who is often misquoted or a sensitive word: check before publishing. Published: {{ $counts['published'] ?? 0 }} · In review: {{ $counts['in_review'] ?? 0 }} · Rejected: {{ $counts['rejected'] ?? 0 }}</p>
    @if (session('status'))<p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-800">{{ session('status') }}</p>@endif
    @if (session('reasons'))<ul class="mt-2 list-disc rounded-lg bg-amber-50 px-8 py-2 text-xs text-amber-800">@foreach (session('reasons') as $r)<li>{{ $r }}</li>@endforeach</ul>@endif
    @error('import')<p class="mt-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700">{{ $message }}</p>@enderror

    <form method="POST" action="{{ route('admin.quotes.import') }}" class="mt-4 flex items-end gap-2 text-sm">
        @csrf
        <label>Import next <input type="number" name="amount" value="200" min="1" max="1000" class="w-24 rounded border-slate-300"> quotes from QuoteSlate</label>
        <button class="rounded-lg bg-brand-600 px-4 py-2 font-semibold text-white">Import as In review</button>
    </form>

    <form method="GET" class="mt-6 flex flex-wrap items-end gap-2 text-sm">
        <select name="status" class="rounded border-slate-300">@foreach (\App\Models\Quote::STATUSES + ['all' => 'All'] as $k => $l)<option value="{{ $k }}" @selected($status === $k)>{{ $l }}</option>@endforeach</select>
        <select name="topic" class="rounded border-slate-300"><option value="">All topics</option>@foreach (\App\Content\QuoteTopics::ALL as $k => $t)<option value="{{ $k }}" @selected($topic === $k)>{{ $t[0] }}</option>@endforeach</select>
        <label><input type="checkbox" name="flagged" value="1" @checked(request('flagged'))> Flagged only</label>
        <label><input type="checkbox" name="clean" value="1" @checked(request('clean'))> No flags</label>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search text or author" class="rounded border-slate-300">
        <button class="rounded-lg bg-slate-800 px-4 py-2 font-semibold text-white">Filter</button>
    </form>

    <form method="POST" action="{{ route('admin.quotes.bulk') }}">
        @csrf
        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <button name="action" value="publish" class="rounded-lg bg-emerald-600 px-3 py-1.5 font-semibold text-white">Publish selected</button>
            <button name="action" value="reject" class="rounded-lg bg-red-600 px-3 py-1.5 font-semibold text-white">Reject selected</button>
            <button name="action" value="review" class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold">Back to review</button>
        </div>
        <table class="mt-3 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
            <thead class="bg-slate-50 text-left text-slate-500"><tr><th class="px-3 py-2"><input type="checkbox" onchange="document.querySelectorAll('.qt-row').forEach(c => c.checked = this.checked)"></th><th class="px-3 py-2">Quote</th><th class="px-3 py-2">Topic</th><th class="px-3 py-2">Status</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($quotes as $q)
                    <tr class="align-top">
                        <td class="px-3 py-2"><input type="checkbox" name="ids[]" value="{{ $q->id }}" class="qt-row"></td>
                        <td class="px-3 py-2"><p>“{{ $q->body }}”</p><p class="text-xs font-semibold text-slate-600">— {{ $q->author }}</p>
                            @foreach ($q->flags ?? [] as $f)<span class="mr-1 mt-1 inline-block rounded bg-amber-100 px-1.5 py-0.5 text-xs text-amber-800">⚠ {{ $f }}</span>@endforeach</td>
                        <td class="px-3 py-2 text-slate-600">{{ \App\Content\QuoteTopics::label($q->topic) }}<br><span class="text-xs">{{ $q->tags }}</span></td>
                        <td class="px-3 py-2"><span class="rounded-full px-2 py-0.5 text-xs {{ ['published' => 'bg-emerald-100 text-emerald-700', 'rejected' => 'bg-red-100 text-red-700'][$q->status] ?? 'bg-slate-100' }}">{{ \App\Models\Quote::STATUSES[$q->status] }}</span>
                            @if ($q->meta?->publish_at)<br><span class="text-xs text-slate-500">scheduled {{ $q->meta->publish_at->format('m-d-Y H:i') }}</span>@endif</td>
                        <td class="px-3 py-2 text-right"><a href="{{ route('admin.quotes.edit', $q) }}" class="text-navy-600 hover:underline">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">No quotes here yet. Use Import above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </form>
    <div class="mt-4">{{ $quotes->links() }}</div>
</x-admin-layout>
