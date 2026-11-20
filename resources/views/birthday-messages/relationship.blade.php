@php
    $toneIntro = [
        'funny' => 'Light-hearted wishes for a group chat, a story post or anyone who loves a laugh.',
        'emotional' => 'Heartfelt wishes for a card or a private message, when you want to say what they mean to you.',
        'short' => 'One or two lines for a quick text, a comment or a cake topper.',
    ];
    $count = $wishes->flatten()->count();
@endphp
<x-layout :title="$guide['title'].' — Funny, Emotional & Short | BuntyFun'"
          :description="$count.' original birthday wishes for your '.Str::lower($guide['label']).', grouped by tone, with tips on how to make the message personal.'"
          :breadcrumbs="['Birthday Wishes' => route('birthday-messages.index'), $guide['label'] => null]">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ $guide['title'] }}</h1>
        <p class="mt-4 leading-relaxed text-slate-700">{{ $guide['intro'] }}</p>

        @foreach ($wishes as $tone => $items)
            <section class="mt-10">
                <h2 class="text-xl font-bold">{{ Str::headline($tone) }} birthday wishes for your {{ Str::lower($guide['label']) }}</h2>
                @isset($toneIntro[$tone])<p class="mt-1 text-sm text-slate-500">{{ $toneIntro[$tone] }}</p>@endisset
                <div class="mt-4 space-y-3">
                    @foreach ($items as $wish)
                        <div class="rounded-xl border border-slate-200 bg-white p-5" x-data="{ copied: false }" @if ($wish->language === 'ur') dir="rtl" @endif>
                            <p class="leading-relaxed text-slate-800">{{ $wish->body }}</p>
                            <div class="mt-3 flex flex-wrap gap-2 text-sm" dir="ltr">
                                <button type="button" class="rounded-full border border-slate-300 px-3 py-1 hover:bg-slate-50"
                                        @click="navigator.clipboard.writeText(@js($wish->body)); copied = true; setTimeout(() => copied = false, 2000)">
                                    <span x-show="!copied">Copy</span><span x-show="copied" x-cloak>Copied!</span>
                                </button>
                                <a href="https://wa.me/?text={{ rawurlencode($wish->body) }}" target="_blank" rel="noopener" class="rounded-full border border-slate-300 px-3 py-1 hover:bg-slate-50">WhatsApp</a>
                                <a href="{{ route('birthday-messages.show', $wish) }}" class="rounded-full px-3 py-1 text-slate-500 hover:underline">Share link</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <section class="mt-12 rounded-xl bg-violet-50 p-6 prose-page">
            <h2 class="!mt-0">How to make it personal</h2>
            <ul>
                @foreach ($guide['tips'] as $tip)
                    <li>{{ $tip }}</li>
                @endforeach
            </ul>
            <h3>What to avoid</h3>
            <p>{{ $guide['avoid'] }}</p>
            <h3>Example</h3>
            <p>{{ $guide['example'] }}</p>
        </section>

        @if ($others->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-lg font-bold">Wishes for someone else</h2>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($others as $r)
                        <a href="{{ route('birthday-messages.relationship', \App\Models\BirthdayMessage::relationshipSlug($r)) }}"
                           class="rounded-full border border-slate-200 bg-white px-4 py-1.5 text-sm hover:border-violet-300">{{ \App\Content\BirthdayGuides::for($r)['label'] }}</a>
                    @endforeach
                </div>
            </section>
        @endif
        <p class="mt-8 text-sm text-slate-500">Want something more tailored? Try the <a href="{{ route('ai-tools.index') }}" class="text-violet-700 underline">AI birthday message generator</a>.</p>
    </div>
</x-layout>
