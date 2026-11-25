@php
    // source_ref is free text like 'Wikipedia, "Ahmad": https://…' (sometimes several, separated by ';').
    // Escape it, then turn each URL into a link.
    $sourceHtml = preg_replace('~https?://[^\s;,<]+~', '<a href="$0" rel="noopener nofollow" target="_blank" class="text-navy-600 underline">$0</a>', e((string) $name->source_ref));
    $genderLabel = ['male' => 'Boy', 'female' => 'Girl', 'unisex' => 'Boy or girl'][$name->gender] ?? Str::headline($name->gender);
@endphp
<x-layout :title="$name->name_en.' Name Meaning ('.$genderLabel.') — Arabic & Urdu Spelling | BuntyFun'"
          :description="$name->name_en.($name->name_ar ? ' ('.$name->name_ar.')' : '').': '.Str::limit($name->meaning, 120).' Source cited.'"
          :meta="$name->meta" :player="true" :breadcrumbs="['Islamic Names' => route('name-entries.index'), $name->name_en => null]">
    <div class="mx-auto max-w-xl px-4 py-12">
        <div class="flex flex-wrap items-center gap-3"><h1 class="text-3xl font-bold">{{ $name->name_en }}</h1>@include('name-entries.partials.play', ['name' => $name, 'full' => true])</div>
        <p class="mt-1 text-slate-500">Name meaning and spelling</p>

        <dl class="mt-6 grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 rounded-xl border border-slate-200 bg-white p-5 text-sm">
            @if ($name->name_ar)
                <dt class="text-slate-500">Arabic</dt><dd class="text-2xl" dir="rtl" lang="ar">{{ $name->name_ar }}</dd>
            @endif
            @if ($name->name_ur)
                <dt class="text-slate-500">Urdu</dt><dd class="font-urdu text-xl" dir="rtl" lang="ur">{{ $name->name_ur }}</dd>
            @endif
            <dt class="text-slate-500">English spelling</dt><dd>{{ $name->name_en }}</dd>
            @if ($name->pronunciation)
                <dt class="text-slate-500">Say it</dt><dd><span class="font-medium">{{ $name->pronunciation }}</span> <span class="text-xs text-slate-500">(capitals = stressed part)</span></dd>
            @endif
            <dt class="text-slate-500">Gender</dt><dd>{{ $genderLabel }}</dd>
            @if ($name->origin)
                <dt class="text-slate-500">Origin</dt><dd>{{ $name->origin }}</dd>
            @endif
            @if (($name->category ?? 'general') !== 'general')
                <dt class="text-slate-500">Type</dt><dd>{{ \App\Models\NameEntry::CATEGORIES[$name->category] ?? Str::headline($name->category) }}</dd>
            @endif
            @if ($name->quranic_ref)
                @php $qm = preg_match('/(\d+):(\d+)/', $name->quranic_ref, $mm) ? 'https://quran.com/'.$mm[1].'/'.$mm[2] : null; @endphp
                <dt class="text-slate-500">In the Quran</dt><dd>@if ($qm)<a href="{{ $qm }}" rel="noopener nofollow" target="_blank" class="text-navy-600 underline">{{ $name->quranic_ref }}</a>@else{{ $name->quranic_ref }}@endif</dd>
            @endif
        </dl>

        <h2 class="mt-8 text-lg font-bold">Meaning of {{ $name->name_en }}</h2>
        <p class="mt-2 text-lg leading-relaxed">{{ $name->meaning }}</p>
        @if ($name->meaning_ur)
            <h2 class="mt-6 text-lg font-bold">Meaning in Urdu</h2>
            <p class="font-urdu mt-2 text-xl leading-loose" dir="rtl" lang="ur">{{ $name->meaning_ur }}</p>
        @endif

        @if (in_array($name->verification, ['uncertain', 'needs_review'], true))
            <p class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">{{ $name->verification === 'uncertain' ? 'The meaning of this name is uncertain; scholars and dictionaries differ.' : 'This meaning has not yet been checked by an editor.' }}</p>
        @endif
        <p class="mt-6 text-sm text-slate-600">
            Source:
            {!! $sourceHtml !!}
        </p>
        <p class="mt-4 rounded-lg bg-slate-100 p-4 text-xs leading-relaxed text-slate-600">
            English spellings of Arabic names vary (for example Mohammed / Muhammad, Hussein / Husain); the Arabic spelling above is the reference form. Meanings describe the word's linguistic root and are not a religious ruling. Spotted an error? <a href="{{ route('pages.contact') }}" class="underline">Tell us</a>. How we check names: <a href="{{ route('pages.editorial') }}" class="underline">editorial policy</a>.
        </p>

        @if ($related->isNotEmpty())
            <div class="mt-10">
                <h2 class="text-lg font-semibold">More {{ Str::lower($genderLabel) }} names</h2>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    @foreach ($related as $r)
                        <a href="{{ route('name-entries.show', $r) }}" class="rounded-lg border border-slate-200 bg-white p-3 text-sm hover:shadow-sm">
                            <span class="font-medium">{{ $r->name_en }}</span>
                            <span class="block text-xs text-slate-500">{{ Str::limit($r->meaning, 50) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <a href="{{ route('name-entries.index') }}" class="mt-8 block text-sm text-slate-500 hover:underline">Browse all names</a>
        <x-explore group="islamic" title="More Islamic names and resources" />
    </div>
</x-layout>
