<x-layout :title="($q ? 'Quran search: '.$q : 'Search the Quran').' | BuntyFun'" description="Search the Quran in Arabic or in the translation." :noindex="true"
          :breadcrumbs="['Quran' => route('quran.index'), 'Search' => null]" :quranFont="true" :urduFont="true">
    <div class="mx-auto max-w-4xl px-4 py-10">
        <h1 class="text-3xl font-bold">Search the Quran</h1>
        <form method="GET" class="mt-4 flex max-w-xl gap-2" role="search">
            <label for="q" class="sr-only">Search</label>
            <input id="q" type="search" name="q" value="{{ $q }}" placeholder="Arabic word or English/Urdu words" class="w-full rounded-full border-slate-300 px-4 py-2">
            <button class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white">Search</button>
        </form>
        <p class="mt-2 text-xs text-slate-500">Arabic search ignores diacritics (harakat). Up to 50 verses are shown.</p>
        @if ($nameHits->isNotEmpty())
            <h2 class="mt-8 text-lg font-bold">Surahs</h2>
            <ul class="mt-2 flex flex-wrap gap-2">@foreach ($nameHits as $s)<li><a href="{{ $s->url() }}" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium hover:border-emerald-600">{{ $s->number }}. {{ $s->name_en }}</a></li>@endforeach</ul>
        @endif
        @if ($q !== '')
            <h2 class="mt-8 text-lg font-bold">{{ $results->count() }} {{ Str::plural('verse', $results->count()) }}</h2>
            <ol class="mt-3 space-y-3">
                @foreach ($results as $a)
                    @php $s = $surahs[$a->surah]; @endphp
                    <li class="rounded-xl border border-slate-200 bg-white p-4">
                        <a href="{{ $s->url() }}#ayah-{{ $a->ayah }}" class="text-sm font-semibold text-emerald-700 hover:underline">{{ $s->name_en }} {{ $a->surah }}:{{ $a->ayah }}</a>
                        <p class="font-quran mt-2 text-right text-2xl" dir="rtl" lang="ar">{{ $a->text_uthmani }}</p>
                        @foreach ($texts[$a->id] ?? [] as $t)<p class="mt-2 text-sm text-slate-700">{{ $t->text }}</p>@endforeach
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</x-layout>
