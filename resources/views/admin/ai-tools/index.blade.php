<x-admin-layout title="AI Tools">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">AI Tools</h1>
        <a href="{{ route('admin.ai-tools.create') }}" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">New Tool</a>
    </div>
    <p class="mt-1 text-sm text-slate-500">
        Active provider: <strong>{{ config('ai.provider') }}</strong> ·
        Key configured: <strong>{{ config('ai.'.config('ai.provider').'.key') ? 'yes' : 'no — generation will fail until GEMINI_API_KEY/OPENAI_API_KEY is set' }}</strong> ·
        Daily free limit: <strong>{{ config('ai.daily_free_limit') }}</strong> per tool per visitor
    </p>

    <table class="mt-6 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
                <th class="px-4 py-2">Title</th>
                <th class="px-4 py-2">Slug</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($tools as $tool)
                <tr>
                    <td class="px-4 py-2">{{ $tool->title }}</td>
                    <td class="px-4 py-2 text-slate-500">{{ $tool->slug }}</td>
                    <td class="px-4 py-2">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $tool->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ Str::headline($tool->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <a href="{{ route('admin.ai-tools.edit', $tool) }}" class="text-violet-600 hover:underline">Edit</a>
                        @if (auth()->user()->canPublish())
                            @if ($tool->status === 'published')
                                <form method="POST" action="{{ route('admin.ai-tools.unpublish', $tool) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="ml-3 text-slate-500 hover:underline">Unpublish</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.ai-tools.publish', $tool) }}" class="inline">
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
</x-admin-layout>
