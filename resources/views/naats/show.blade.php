@php
    $schema = [];
    if ($naat->audio) {
        $schema[] = ['@context' => 'https://schema.org', '@type' => 'AudioObject', 'name' => $naat->title, 'contentUrl' => $naat->audio->src(),
            'inLanguage' => $naat->category === 'arabic' ? 'ar' : 'ur', 'creditText' => $naat->reciter, 'license' => $naat->audio->source_url ?: null,
            'description' => Str::limit(strip_tags((string) ($naat->description ?: $naat->title)), 150)];
    }
@endphp
<x-layout :title="$naat->title.($naat->reciter ? ' — '.$naat->reciter : '').' | Naat with Lyrics | BuntyFun'"
          :description="Str::limit(($naat->description ?: $naat->title).($naat->poet ? ' Poet: '.$naat->poet.'.' : '').($naat->reciter ? ' Recited by '.$naat->reciter.'.' : ''), 155)"
          :meta="$naat->meta" :schema="$schema" :urduFont="true" :player="true"
          :breadcrumbs="['Naats' => route('naats.index'), (\App\Models\Naat::CATEGORIES[$naat->category] ?? 'Naats') => route('naats.category', $naat->category), $naat->title => null]">
    <div class="mx-auto max-w-2xl px-4 py-10">
        <h1 class="text-2xl font-bold sm:text-3xl">{{ $naat->title }}</h1>
        @if ($naat->title_ur)<p class="font-urdu mt-2 text-right text-2xl" dir="rtl" lang="ur">{{ $naat->title_ur }}</p>@endif
        <dl class="mt-4 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm">
            @if ($naat->poet)<dt class="text-slate-500">Poet</dt><dd>{{ $naat->poet }}</dd>@endif
            @if ($naat->reciter)<dt class="text-slate-500">Reciter</dt><dd>{{ $naat->reciter }}</dd>@endif
            <dt class="text-slate-500">Category</dt><dd>{{ \App\Models\Naat::CATEGORIES[$naat->category] ?? $naat->category }}</dd>
        </dl>
        @if ($naat->audio)
            <button type="button" @click="BFPlayer.load([{ src: {{ Js::from($naat->audio->src()) }}, title: {{ Js::from($naat->title) }}, subtitle: {{ Js::from($naat->reciter ?: 'Naat') }}, key: 'naat:{{ $naat->id }}' }], 0)"
                    class="mt-5 inline-flex items-center gap-2 rounded-full bg-emerald-700 px-6 py-3 font-semibold text-white hover:bg-emerald-800">▶ Play naat</button>
        @elseif ($naat->youtube_id)
            <div class="mt-5 aspect-video overflow-hidden rounded-xl bg-black">
                <iframe class="h-full w-full" src="https://www.youtube-nocookie.com/embed/{{ $naat->youtube_id }}" title="{{ $naat->title }} (YouTube)" loading="lazy"
                        allow="accelerometer; encrypted-media; picture-in-picture" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
            </div>
            <p class="mt-1 text-xs text-slate-500">Embedded from the recording's official YouTube channel.</p>
        @endif
        @if ($naat->description)<p class="mt-6 leading-relaxed text-slate-700">{{ $naat->description }}</p>@endif
        @if ($naat->lyrics)
            <h2 class="mt-8 text-lg font-bold">Lyrics</h2>
            <x-speak :text="$naat->lyrics" lang="ur" :full="true" label="Read the lyrics aloud" class="mt-2" />
            <div class="font-urdu mt-3 whitespace-pre-line rounded-xl border border-slate-200 bg-white p-5 text-right text-xl leading-[2.4]" dir="rtl" lang="ur">{{ $naat->lyrics }}</div>
        @endif
        <p class="mt-6 text-xs text-slate-500">Licence: {{ $naat->audio?->license ?: ($naat->license ?: 'Text shown for reading only.') }}@if ($naat->source_url) · <a href="{{ $naat->source_url }}" class="underline" rel="noopener nofollow" target="_blank">source</a>@endif</p>
        @if ($more->isNotEmpty())
            <h2 class="mt-10 text-lg font-bold">More {{ Str::lower(\App\Models\Naat::CATEGORIES[$naat->category] ?? 'naats') }}</h2>
            <ul class="mt-3 space-y-2">@foreach ($more as $m)<li><a href="{{ route('naats.show', $m) }}" class="text-navy-600 underline">{{ $m->title }}</a></li>@endforeach</ul>
        @endif
        <x-explore group="islamic" />
    </div>
</x-layout>
