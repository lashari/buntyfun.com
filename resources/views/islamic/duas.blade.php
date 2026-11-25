<x-layout title="Masnoon Duas in Arabic with English & Urdu Translation | BuntyFun"
          description="Masnoon duas and adhkar for morning and evening, sleep, travel, prayer, protection and daily life, with Arabic, transliteration, English and Urdu meaning, the Quran or hadith reference and its grading."
          :breadcrumbs="['Duas' => null]" :noindex="$groups->isEmpty()" :urduFont="true" :quranFont="true" :player="true">
    <div class="mx-auto max-w-5xl px-4 py-10" x-data="{ q: '' }">
        <h1 class="text-3xl font-bold">Masnoon Duas &amp; Adhkar</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">Supplications from the Quran and the authentic Sunnah for everyday moments. Each has its Arabic text, a transliteration, the meaning in English and Urdu, the Quran verse or hadith it comes from, and the hadith grading, so you can check it yourself. Press <strong>Listen</strong> to hear it: Quranic duas play a recitation of the verse.</p>
        @include('islamic.partials.dua-nav', ['active' => null])
        <label class="mt-5 block max-w-md">
            <span class="sr-only">Search duas</span>
            <input type="search" x-model="q" placeholder="Search duas (e.g. sleep, travel, forgiveness)" class="w-full rounded-full border-slate-300 px-4 py-2">
        </label>
        @forelse ($groups as $cat => $duas)
            <section class="mt-8" x-show="!q || $el.textContent.toLowerCase().includes(q.toLowerCase())">
                <h2 class="text-xl font-bold"><a href="{{ route('duas.category', $cat) }}" class="hover:underline">{{ \App\Models\Dua::CATEGORIES[$cat] ?? Str::headline($cat) }}</a></h2>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach ($duas as $d)
                        <div x-show="!q || $el.textContent.toLowerCase().includes(q.toLowerCase())">@include('islamic.partials.dua-card', ['dua' => $d])</div>
                    @endforeach
                </div>
            </section>
        @empty
            <p class="mt-8 text-slate-500">Duas are being checked by our editors and will be published soon.</p>
        @endforelse
        <p class="mt-10 text-sm text-slate-500">See also the <a href="{{ route('islamic.allah-names') }}" class="underline">99 Names of Allah</a>, the <a href="{{ route('quran.index') }}" class="underline">Quran with audio</a> and the <a href="{{ route('islamic.calendar') }}" class="underline">Islamic calendar</a>. Hadith gradings are shown as given by the scholars named on each dua's page.</p>
        <x-explore group="islamic" />
    </div>
</x-layout>
