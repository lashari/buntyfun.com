<x-admin-layout :title="'Library — '.$t['label']">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Library</h1>
        <a href="{{ route('admin.library.create', $type) }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">New {{ Str::singular($t['label']) }}</a>
    </div>
    <p class="mt-1 text-sm text-slate-600">Imported and new items start <strong>In review</strong>. Read them (check Arabic, Urdu spelling and references), then publish. Archived items stay in the database but are hidden from the site.</p>
    <nav class="mt-4 flex flex-wrap gap-2 text-sm font-semibold">
        @foreach ($tabs as $k => $label)
            <a href="{{ route('admin.library.index', $k) }}" class="rounded-full px-3 py-1 {{ $k === $type ? 'bg-navy-600 text-white' : 'bg-white border border-slate-200' }}">{{ $label }}</a>
        @endforeach
        <a href="{{ route('admin.name-entries.index') }}" class="rounded-full border border-slate-200 bg-white px-3 py-1">Islamic Names</a>
    </nav>
    @if (session('reasons'))<ul class="mt-3 list-disc rounded-lg bg-amber-50 px-8 py-2 text-xs text-amber-800">@foreach (session('reasons') as $r)<li>{{ $r }}</li>@endforeach</ul>@endif

    <form method="GET" class="mt-5 flex flex-wrap items-end gap-2 text-sm">
        <select name="status" class="rounded border-slate-300">
            @foreach (\App\Http\Controllers\Admin\LibraryAdminController::STATUSES + ['all' => 'All'] as $k => $l)<option value="{{ $k }}" @selected($status === $k)>{{ $l }} ({{ $k === 'all' ? $counts->sum() : ($counts[$k] ?? 0) }})</option>@endforeach
        </select>
        @if ($type === 'poems')
            <select name="poet" class="rounded border-slate-300"><option value="">All poets</option>@foreach ($poets as $id => $n)<option value="{{ $id }}" @selected(request('poet') == $id)>{{ $n }}</option>@endforeach</select>
            <select name="form" class="rounded border-slate-300"><option value="">All forms</option>@foreach (\App\Models\Poem::FORMS as $k => $l)<option value="{{ $k }}" @selected(request('form') === $k)>{{ $l[0] }}</option>@endforeach</select>
            <select name="mood" class="rounded border-slate-300"><option value="">Any mood</option><option value="none" @selected(request('mood') === 'none')>No mood</option>@foreach (\App\Models\Poem::MOODS as $k => $l)<option value="{{ $k }}" @selected(request('mood') === $k)>{{ $l[0] }}</option>@endforeach</select>
        @endif
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search" class="rounded border-slate-300">
        <button class="rounded-lg bg-slate-800 px-4 py-2 font-semibold text-white">Filter</button>
    </form>

    <form method="POST" action="{{ route('admin.library.bulk', $type) }}">
        @csrf
        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            @if (auth()->user()->canPublish())<button name="action" value="published" class="rounded-lg bg-emerald-600 px-3 py-1.5 font-semibold text-white">Publish selected</button>@endif
            <button name="action" value="in_review" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-semibold">Back to review</button>
            <button name="action" value="draft" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-semibold">Unpublish (draft)</button>
            <button name="action" value="archived" class="rounded-lg bg-slate-600 px-3 py-1.5 font-semibold text-white">Archive selected</button>
        </div>
        <table class="mt-3 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
            <thead class="bg-slate-50 text-left text-slate-500"><tr><th class="px-3 py-2"><input type="checkbox" onchange="document.querySelectorAll('.lib-row').forEach(c => c.checked = this.checked)" aria-label="Select all"></th><th class="px-3 py-2">Item</th><th class="px-3 py-2">Details</th><th class="px-3 py-2">Status</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $it)
                    <tr class="align-top">
                        <td class="px-3 py-2"><input type="checkbox" name="ids[]" value="{{ $it->id }}" class="lib-row"></td>
                        <td class="px-3 py-2">
                            @switch($type)
                                @case('poems')
                                    <p class="font-urdu text-right text-lg leading-[2.1]" dir="rtl" lang="ur">{{ Str::limit($it->firstLines(2), 160) }}</p>
                                    @break
                                @case('poets')
                                    <p class="font-semibold">{{ $it->name_en }} <span class="font-urdu" lang="ur">{{ $it->name_ur }}</span></p>
                                    @break
                                @case('duas')
                                    <p class="font-semibold">{{ $it->title }}</p><p class="text-right text-lg" dir="rtl" lang="ar">{{ Str::limit($it->arabic, 120) }}</p><p class="text-xs text-slate-600">{{ Str::limit($it->translation_en, 120) }}</p>
                                    @break
                                @default
                                    <p><span class="text-slate-400">{{ $it->number }}.</span> <span class="text-xl" dir="rtl" lang="ar">{{ $it->arabic }}</span> {{ $it->transliteration }}</p>
                            @endswitch
                        </td>
                        <td class="px-3 py-2 text-xs text-slate-600">
                            @switch($type)
                                @case('poems') {{ $it->poet?->name_en }} · {{ \App\Models\Poem::FORMS[$it->form][0] ?? $it->form }} · {{ $it->line_count }} lines @if ($it->mood)<br>mood: {{ $it->mood }}@endif @break
                                @case('poets') {{ $it->years() }} @break
                                @case('duas') {{ \App\Models\Dua::CATEGORIES[$it->category] ?? $it->category }}<br>{{ $it->reference }} @break
                                @default {{ $it->meaning_en }}<br><span class="font-urdu" lang="ur">{{ $it->meaning_ur }}</span>
                            @endswitch
                        </td>
                        <td class="px-3 py-2"><span class="rounded-full px-2 py-0.5 text-xs {{ ['published' => 'bg-emerald-100 text-emerald-700', 'archived' => 'bg-slate-200 text-slate-600', 'in_review' => 'bg-amber-100 text-amber-800'][$it->status] ?? 'bg-slate-100' }}">{{ \App\Http\Controllers\Admin\LibraryAdminController::STATUSES[$it->status] ?? $it->status }}</span></td>
                        <td class="whitespace-nowrap px-3 py-2 text-right">
                            <a href="{{ route('admin.library.edit', [$type, $it->id]) }}" class="text-navy-600 hover:underline">Edit</a>
                            @if ($type === 'poems' && $it->poet)<a href="{{ route('poetry.show', [$it->poet->slug, $it->slug]) }}" target="_blank" class="ml-2 text-slate-500 hover:underline">Preview</a>@endif
                            @if ($type === 'duas' && $it->status === 'published')<a href="{{ route('duas.show', $it) }}" target="_blank" class="ml-2 text-slate-500 hover:underline">View</a>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">Nothing here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </form>
    <div class="mt-4">{{ $items->links() }}</div>

    <details class="mt-8 rounded-xl border border-slate-200 bg-white p-4 text-sm">
        <summary class="cursor-pointer font-semibold">Import {{ $t['label'] }} from JSON or CSV</summary>
        <p class="mt-2 text-slate-600">Columns / keys: <code>{{ implode(', ', array_keys($t['fields'])) }}</code>{{ $type === 'poems' ? ' (or poet = poet slug instead of poet_id)' : '' }}. Max 2,000 rows, 5 MB. Rows that fail checks or already exist are skipped and listed. Everything imported is saved {{ $type === 'poets' ? 'as Draft' : 'In review' }}: nothing is published automatically.</p>
        <form method="POST" action="{{ route('admin.library.import', $type) }}" enctype="multipart/form-data" class="mt-3 flex items-center gap-2">
            @csrf
            <input type="file" name="file" accept=".json,.csv,.txt" required>
            <button class="rounded-lg bg-brand-600 px-4 py-2 font-semibold text-white">Import</button>
        </form>
    </details>
</x-admin-layout>
