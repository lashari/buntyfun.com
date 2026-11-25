<x-layout :breadcrumbs="['AI Fun Studio' => null]" title="AI Fun Studio — Free Joke, Caption, Greeting & Birthday Message Generators | BuntyFun" description="Small free AI tools for jokes, captions, nicknames, greetings, bios and birthday messages. Type a few words, get a result, copy it.">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">AI Fun Studio</h1>
        <div class="mt-3 max-w-3xl space-y-3 leading-relaxed text-slate-700">
            <p>Ten small tools that turn a few words into a joke, caption, nickname, greeting, short story or birthday message. Type a topic, press Generate and copy the result. Each tool explains what to type and shows example ideas.</p>
            <p>Results are created by an AI model (Google Gemini) at the moment you ask, so every result is different. They are not reviewed by a person, harmful requests are blocked, and each tool allows {{ config('ai.daily_free_limit') }} free tries per day. What you type is not saved by BuntyFun.</p>
        </div>
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
        <x-explore group="ai" />
    </div>
</x-layout>
