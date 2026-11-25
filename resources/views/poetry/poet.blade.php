@php
    $schema = [['@context' => 'https://schema.org', '@type' => 'Person', 'name' => $poet->name_en, 'alternateName' => $poet->name_ur,
        'birthDate' => (string) $poet->born, 'deathDate' => (string) $poet->died, 'jobTitle' => 'Poet', 'sameAs' => array_values(array_filter([$poet->wikipedia]))]];
@endphp
<x-layout :title="$poet->name_en.' Poetry — Ghazals & Shayari in Urdu ('.$poet->name_ur.') | BuntyFun'"
          :description="'Read '.$poet->name_en.' ('.$poet->years().') poetry in Urdu: '.Str::limit($poet->bio_en, 110)"
          :breadcrumbs="['Urdu Poetry' => route('poetry.index'), $poet->name_en => null]" :schema="$schema" :urduFont="true"
          :noindex="request()->hasAny(['page', 'form'])">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ $poet->name_en }} <span class="font-urdu font-normal text-slate-500" lang="ur">{{ $poet->name_ur }}</span></h1>
        <p class="mt-1 text-slate-500">{{ $poet->years() }}</p>
        <div class="mt-4 grid gap-6 lg:grid-cols-2">
            <p class="leading-relaxed text-slate-700">{{ $poet->bio_en }}</p>
            @if ($poet->bio_ur)
                <div>
                    <p class="font-urdu text-lg leading-[2.2] text-slate-700" dir="rtl" lang="ur">{{ $poet->bio_ur }}</p>
                    <p class="mt-1 text-xs text-slate-400">Urdu introduction from Urdu Wikipedia (CC BY-SA).</p>
                </div>
            @endif
        </div>
        <nav class="mt-6 flex flex-wrap gap-2 text-sm font-semibold">
            <a href="{{ route('poetry.segment', $poet->slug) }}" class="rounded-full px-3 py-1 {{ ! $form ? 'bg-navy-600 text-white' : 'bg-slate-100' }}">All ({{ $forms->sum() }})</a>
            @foreach ($forms as $f => $c)
                <a href="{{ route('poetry.segment', [$poet->slug, 'form' => $f]) }}" class="rounded-full px-3 py-1 {{ $form === $f ? 'bg-navy-600 text-white' : 'bg-slate-100' }}">{{ \App\Models\Poem::FORMS[$f][0] ?? $f }} ({{ $c }})</a>
            @endforeach
        </nav>
        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($poems as $poem)
                @php $poem->setRelation('poet', $poet); @endphp
                @include('poetry.partials.card')
            @empty
                <p class="text-slate-500">Poems by {{ $poet->name_en }} are being checked and will appear soon.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $poems->links() }}</div>
    </div>
</x-layout>
