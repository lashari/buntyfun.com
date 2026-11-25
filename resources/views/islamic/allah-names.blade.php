@php
    $schema = $names->isEmpty() ? [] : [[
        '@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => '99 Names of Allah (Asma ul Husna)',
        'itemListElement' => $names->map(fn ($n) => ['@type' => 'ListItem', 'position' => $n->number, 'name' => $n->transliteration.' ('.$n->arabic.') — '.$n->meaning_en])->all(),
    ]];
@endphp
<x-layout title="99 Names of Allah (Asma ul Husna) with Meaning in English & Urdu | BuntyFun"
          description="All 99 Names of Allah in Arabic with English transliteration, English meaning and Urdu meaning. Learn one name a day and test yourself."
          :breadcrumbs="['Islamic Names' => route('name-entries.index'), '99 Names of Allah' => null]" :schema="$schema" :noindex="$names->isEmpty()" :urduFont="true">
    <div class="mx-auto max-w-6xl px-4 py-10" x-data="{ q: '', show: 'all' }">
        <h1 class="text-3xl font-bold">99 Names of Allah <span class="font-normal text-slate-500">(Asma ul Husna)</span></h1>
        <div class="mt-3 max-w-3xl space-y-3 leading-relaxed text-slate-700">
            <p>"Allah has ninety-nine names; whoever memorises them will enter Paradise" (Sahih al-Bukhari 7392, Sahih Muslim 2677). The list below follows the order narrated in Jami' at-Tirmidhi (3507), with the Arabic, a common English spelling, the meaning in English and the meaning in Urdu.</p>
            <p>Translations can only give part of each name's meaning. Learn a few at a time: say the name, read its meaning, and think of it in your duas.</p>
        </div>
        @if ($names->isNotEmpty())
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <input type="search" x-model="q" placeholder="Search a name or meaning" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" aria-label="Search the 99 names">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" @change="show = $event.target.checked ? 'learn' : 'all'"> Hide meanings (test yourself)</label>
            </div>
            <ol class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($names as $n)
                    <li x-show="!q || {{ Js::from(Str::lower($n->transliteration.' '.$n->meaning_en.' '.$n->meaning_ur.' '.$n->arabic)) }}.includes(q.toLowerCase())"
                        class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm" x-data="{ open: false }">
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-xs font-semibold text-slate-400">{{ $n->number }}</span>
                            <span class="text-3xl leading-snug text-navy-700" dir="rtl" lang="ar">{{ $n->arabic }}</span>
                        </div>
                        <p class="mt-2 flex items-center justify-between gap-2 font-semibold">{{ $n->transliteration }} <x-speak :text="$n->arabic" lang="ar" label="Listen to {{ $n->transliteration }}" /></p>
                        <div x-show="show === 'all' || open" class="mt-1">
                            <p class="text-sm text-slate-600">{{ $n->meaning_en }}</p>
                            @if ($n->meaning_ur)<p class="font-urdu mt-1 text-lg leading-loose text-slate-700" dir="rtl" lang="ur">{{ $n->meaning_ur }}</p>@endif
                        </div>
                        <button type="button" x-show="show === 'learn' && !open" @click="open = true" class="mt-2 text-sm text-navy-600 underline">Show meaning</button>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="mt-8 text-slate-500">This page is being checked by our editors and will be published soon.</p>
        @endif
        <p class="mt-8 text-sm text-slate-500">Spotted a mistake? <a href="{{ route('pages.contact') }}" class="underline">Tell us</a>. See also: <a href="{{ route('duas.index') }}" class="underline">daily duas</a>, <a href="{{ route('name-entries.list', 'prophets') }}" class="underline">names of the prophets</a>, <a href="{{ route('islamic.calendar') }}" class="underline">Islamic calendar</a>.</p>
        <x-explore group="islamic" />
    </div>
</x-layout>
