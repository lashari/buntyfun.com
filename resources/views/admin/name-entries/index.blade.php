<x-admin-layout title="Islamic Names">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Islamic Names</h1>
        <a href="{{ route('admin.name-entries.create') }}" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">New Name</a>
    </div>

    <table class="mt-6 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Source</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($names as $name)
                <tr>
                    <td class="px-4 py-2">{{ $name->name_en }}</td>
                    <td class="px-4 py-2 text-slate-500">{{ Str::limit($name->source_ref, 40) }}</td>
                    <td class="px-4 py-2">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $name->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ Str::headline($name->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <a href="{{ route('admin.name-entries.edit', $name) }}" class="text-violet-600 hover:underline">Edit</a>
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
