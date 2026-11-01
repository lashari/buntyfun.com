<x-layout title="AI Fun Studio — BuntyFun" description="Free AI-powered tools — jokes, roasts, nicknames, captions and more.">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">AI Fun Studio</h1>
        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            @forelse ($tools as $tool)
                <a href="{{ route('ai-tools.show', $tool) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                    <h2 class="font-semibold">{{ $tool->title }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $tool->description }}</p>
                </a>
            @empty
                <p class="text-sm text-slate-500">No AI tools published yet.</p>
            @endforelse
        </div>
    </div>
</x-layout>
