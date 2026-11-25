<x-layout :title="$dua->title.' — Arabic, Meaning in English & Urdu | BuntyFun'"
          :description="$dua->title.': '.Str::limit($dua->translation_en, 110).' ('.$dua->reference.')'"
          :meta="$dua->meta" :breadcrumbs="['Duas' => route('duas.index'), $dua->title => null]" :urduFont="true">
    <div class="mx-auto max-w-2xl px-4 py-10" x-data="{ copied: false }">
        <h1 class="text-2xl font-bold sm:text-3xl">{{ $dua->title }}</h1>
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-right text-3xl leading-[2.4] text-navy-800" dir="rtl" lang="ar">{{ $dua->arabic }}</p>
            @if ($dua->transliteration)<p class="mt-5 italic text-slate-700">{{ $dua->transliteration }}</p>@endif
            <h2 class="mt-5 text-sm font-semibold uppercase tracking-wide text-slate-500">Meaning</h2>
            <p class="mt-1 text-lg leading-relaxed">{{ $dua->translation_en }}</p>
            @if ($dua->translation_ur)
                <p class="font-urdu mt-4 text-xl leading-loose" dir="rtl" lang="ur">{{ $dua->translation_ur }}</p>
            @endif
            <p class="mt-5 text-sm text-slate-600">Reference:
                @if ($dua->source_url)<a href="{{ $dua->source_url }}" rel="noopener nofollow" target="_blank" class="text-navy-600 underline">{{ $dua->reference }}</a>@else{{ $dua->reference }}@endif
            </p>
            <div class="mt-5 flex flex-wrap gap-2">
                <button type="button" @click="BF.copy({{ Js::from($dua->arabic."\n".$dua->translation_en."\n(".$dua->reference.')') }}).then(ok => copied = ok)" class="rounded-full bg-slate-200 px-4 py-2 text-sm font-semibold"><span x-text="copied ? 'Copied ✓' : 'Copy'"></span></button>
                <button type="button" @click="BF.share({{ Js::from($dua->title.":\n".$dua->arabic."\n".$dua->translation_en) }})" class="rounded-full bg-emerald-700 px-4 py-2 text-sm font-semibold text-white">Share on WhatsApp</button>
            </div>
        </div>
        @if ($more->isNotEmpty())
            <h2 class="mt-10 text-lg font-bold">More duas: {{ \App\Models\Dua::CATEGORIES[$dua->category] ?? Str::headline($dua->category) }}</h2>
            <ul class="mt-3 space-y-2">
                @foreach ($more as $m)<li><a href="{{ route('duas.show', $m) }}" class="text-navy-600 underline">{{ $m->title }}</a></li>@endforeach
            </ul>
        @endif
        <a href="{{ route('duas.index') }}" class="mt-8 block text-sm text-slate-500 hover:underline">All duas</a>
        <x-explore group="islamic" title="More Islamic resources" />
    </div>
</x-layout>
