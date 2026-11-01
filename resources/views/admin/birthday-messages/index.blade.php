<x-admin-layout title="Birthday Wishes">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Birthday Wishes</h1>
        <a href="{{ route('admin.birthday-messages.create') }}" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">New Wish</a>
    </div>

    <table class="mt-6 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
                <th class="px-4 py-2">Message</th>
                <th class="px-4 py-2">Relationship / Tone</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($messages as $message)
                <tr>
                    <td class="px-4 py-2">{{ Str::limit($message->body, 60) }}</td>
                    <td class="px-4 py-2 text-slate-500">{{ Str::headline($message->relationship) }} / {{ Str::headline($message->tone) }}</td>
                    <td class="px-4 py-2">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $message->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ Str::headline($message->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <a href="{{ route('admin.birthday-messages.edit', $message) }}" class="text-violet-600 hover:underline">Edit</a>
                        @if (auth()->user()->canPublish())
                            @if ($message->status === 'published')
                                <form method="POST" action="{{ route('admin.birthday-messages.unpublish', $message) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="ml-3 text-slate-500 hover:underline">Unpublish</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.birthday-messages.publish', $message) }}" class="inline">
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
    <div class="mt-4">{{ $messages->links() }}</div>
</x-admin-layout>
