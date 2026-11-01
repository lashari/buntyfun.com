<x-admin-layout title="Urdu Content">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Urdu Content</h1>
        <a href="{{ route('admin.urdu-content.create') }}" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">New Item</a>
    </div>
    <p class="mt-1 text-sm text-slate-500">Human fluency review required before publishing — AI-drafted Urdu is not a substitute for a native/fluent speaker's sign-off.</p>

    <table class="mt-6 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
                <th class="px-4 py-2">Body</th>
                <th class="px-4 py-2">Category</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($items as $item)
                <tr>
                    <td class="px-4 py-2" dir="rtl">{{ Str::limit($item->body, 60) }}</td>
                    <td class="px-4 py-2 text-slate-500">{{ $item->category }}</td>
                    <td class="px-4 py-2">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $item->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ Str::headline($item->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <a href="{{ route('admin.urdu-content.edit', $item) }}" class="text-violet-600 hover:underline">Edit</a>
                        @if (auth()->user()->canPublish())
                            @if ($item->status === 'published')
                                <form method="POST" action="{{ route('admin.urdu-content.unpublish', $item) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="ml-3 text-slate-500 hover:underline">Unpublish</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.urdu-content.publish', $item) }}" class="inline">
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
    <div class="mt-4">{{ $items->links() }}</div>
</x-admin-layout>
