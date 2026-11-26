@php $label = \App\Models\Dua::CATEGORIES[$category]; @endphp
<x-layout :title="$label.' Duas in Arabic with English & Urdu Meaning | BuntyFun'"
          :description="'Masnoon '.Str::lower($label).' duas with Arabic text, transliteration, English and Urdu meaning, Quran or hadith reference and grading. '.$duas->count().' duas, with audio.'"
          :breadcrumbs="['Duas' => route('duas.index'), $label => null]" :urduFont="true" :quranFont="true" :player="true">
    <div class="mx-auto max-w-5xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ $label }} duas</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">{{ $duas->count() }} {{ Str::plural('dua', $duas->count()) }} from the Quran and the authentic Sunnah, each with its source and grading.</p>
        @include('islamic.partials.dua-nav', ['active' => $category])
        <div class="mt-6 grid gap-3 sm:grid-cols-2">
            @foreach ($duas as $d)
                @include('islamic.partials.dua-card', ['dua' => $d])
            @endforeach
        </div>
        <a href="{{ route('duas.index') }}" class="mt-8 block text-sm text-slate-500 hover:underline">All duas</a>
        <x-explore group="islamic" />
    </div>
</x-layout>
