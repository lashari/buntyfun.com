{{-- One dua in a list: full Arabic (never shortened), reference, grading, Listen. --}}
<article class="flex flex-col rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <h3 class="font-semibold"><a href="{{ route('duas.show', $dua) }}" class="hover:underline">{{ $dua->title }}</a></h3>
    <p class="font-quran mt-2 text-right text-2xl text-navy-800" dir="rtl" lang="ar">{{ $dua->arabic }}</p>
    <p class="mt-2 text-sm text-slate-600">{{ Str::limit($dua->translation_en, 140) }}</p>
    <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-3">
        @include('islamic.partials.listen', ['dua' => $dua])
        <span class="text-xs text-slate-500">{{ $dua->reference }}@if ($dua->grading && $dua->grading !== 'Quran') · {{ $dua->grading }}@endif</span>
    </div>
</article>
