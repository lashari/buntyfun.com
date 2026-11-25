<x-admin-layout title="Islamic Names">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Islamic Names</h1>
        <a href="{{ route('admin.name-entries.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">New Name</a>
    </div>

    <form method="GET" class="mt-4 flex flex-wrap items-end gap-2 text-sm">
        <select name="status" class="rounded border-slate-300"><option value="">All statuses</option>@foreach (['in_review' => 'In review', 'published' => 'Published', 'draft' => 'Draft'] as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }} ({{ $counts[$k] ?? 0 }})</option>@endforeach</select>
        <select name="category" class="rounded border-slate-300"><option value="">All lists</option>@foreach (\App\Models\NameEntry::CATEGORIES as $k => $l)<option value="{{ $k }}" @selected(request('category') === $k)>{{ $l }}</option>@endforeach</select>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search" class="rounded border-slate-300">
        <button class="rounded-lg bg-slate-800 px-4 py-2 font-semibold text-white">Filter</button>
    </form>
    <form method="POST" action="{{ route('admin.name-entries.bulk') }}" id="bulk-names">
        @csrf
        <div class="mt-3 flex flex-wrap gap-2 text-sm">
            @if (auth()->user()->canPublish())<button name="action" value="published" class="rounded-lg bg-emerald-600 px-3 py-1.5 font-semibold text-white">Publish selected</button>@endif
            <button name="action" value="in_review" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-semibold">Back to review</button>
            <button name="action" value="draft" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-semibold">Unpublish</button>
        </div>
    </form>

    <table class="mt-3 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
                <th class="px-4 py-2"><input type="checkbox" onchange="document.querySelectorAll('.nm-row').forEach(c => c.checked = this.checked)" aria-label="Select all"></th>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Source</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($names as $name)
                <tr>
                    <td class="px-4 py-2"><input type="checkbox" form="bulk-names" name="ids[]" value="{{ $name->id }}" class="nm-row"></td>
                    <td class="px-4 py-2">{{ $name->name_en }} @if ($name->name_ar)<span class="ml-1 text-lg" dir="rtl" lang="ar">{{ $name->name_ar }}</span>@endif
                        <span class="block text-xs text-slate-500">{{ \App\Models\NameEntry::CATEGORIES[$name->category ?? 'general'] ?? '' }}@if ($name->quranic_ref) · {{ $name->quranic_ref }}@endif · {{ Str::headline($name->verification ?? '') }}</span></td>
                    <td class="px-4 py-2 text-slate-500">{{ Str::limit($name->source_ref, 40) }}</td>
                    <td class="px-4 py-2">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $name->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ Str::headline($name->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <a href="{{ route('admin.name-entries.edit', $name) }}" class="text-navy-600 hover:underline">Edit</a>
                        @if (auth()->user()->canPublish())
                            @if ($name->status === 'published')
                                <form method="POST" action="{{ route('admin.name-entries.unpublish', $name) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="ml-3 text-slate-500 hover:underline">Unpublish</button>
                                </form>
                            @elseif (str_starts_with($name->source_ref, 'PLACEHOLDER'))
                                <span class="ml-3 text-xs text-amber-600" title="source_ref is still a placeholder">Needs source</span>
                            @else
                                <form method="POST" action="{{ route('admin.name-entries.publish', $name) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="ml-3 text-emerald-600 hover:underline">Publish</button>
                                </form>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $names->links() }}</div>
</x-admin-layout>
