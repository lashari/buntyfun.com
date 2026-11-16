<x-admin-layout title="Messages & Quotes">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Messages &amp; Quotes</h1>
        <a href="{{ route('admin.messages.create') }}" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">New Message</a>
    </div>
    <form method="GET" class="mt-4">
        <select name="category" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">All categories</option>
            @foreach (\App\Models\Message::CATEGORIES as $key => [$label])
                <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    <table class="mt-4 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr><th class="px-4 py-2">Message</th><th class="px-4 py-2">Category</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($items as $item)
                <tr>
                    <td class="px-4 py-2" @if ($item->language === 'ur') dir="rtl" @endif>
                        @if ($item->title)<span class="font-medium">{{ $item->title }}</span> — @endif{{ Str::limit($item->body, 70) }}
                    </td>
                    <td class="px-4 py-2 text-slate-500">{{ $item->categoryLabel() }}</td>
                    <td class="px-4 py-2">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $item->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ Str::headline($item->status) }}</span>
                    </td>
                    <td class="px-4 py-2 text-right whitespace-nowrap">
                        <a href="{{ route('admin.messages.edit', $item) }}" class="text-violet-600 hover:underline">Edit</a>
                        @if (auth()->user()->canPublish())
                            <form method="POST" action="{{ route($item->status === 'published' ? 'admin.messages.unpublish' : 'admin.messages.publish', $item) }}" class="inline">
                                @csrf
                                <button type="submit" class="ml-3 {{ $item->status === 'published' ? 'text-slate-500' : 'text-emerald-600' }} hover:underline">{{ $item->status === 'published' ? 'Unpublish' : 'Publish' }}</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No messages yet. Write one, or generate drafts in AI Studio.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $items->links() }}</div>
</x-admin-layout>
