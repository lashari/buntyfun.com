@php $catLabel = $category ? \App\Models\Naat::CATEGORIES[$category] : null; @endphp
<x-layout :title="($catLabel ?: 'Naat Library — Naat Sharif, Hamd & Salam').' with Audio | BuntyFun'"
          :description="($catLabel ?: 'Naat Sharif, hamd and salam').': lyrics in Urdu, poet and reciter details, and licensed audio recordings.'"
          :breadcrumbs="$category ? ['Naats' => route('naats.index'), $catLabel => null] : ['Naats' => null]"
          :noindex="$naats->isEmpty() || $q !== ''" :urduFont="true" :player="true">
    <div class="mx-auto max-w-5xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ $catLabel ?: 'Naat Library' }}</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">Naats in praise of the Prophet Muhammad ﷺ, hamd and salam, with the poet and reciter named on every page. We only publish recordings we have permission or a licence to share.</p>
        <nav class="mt-6 flex flex-wrap gap-2 text-sm" aria-label="Naat categories">
            <a href="{{ route('naats.index') }}" class="rounded-full border px-3 py-1.5 font-semibold {{ ! $category ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-slate-200 bg-white' }}">All</a>
            @foreach (\App\Models\Naat::CATEGORIES as $k => $l)
                @if (($counts[$k] ?? 0) > 0)<a href="{{ route('naats.category', $k) }}" class="rounded-full border px-3 py-1.5 font-semibold {{ $category === $k ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-slate-200 bg-white' }}">{{ $l }} ({{ $counts[$k] }})</a>@endif
            @endforeach
        </nav>
        @if (! $category)
            <form method="GET" class="mt-5 flex max-w-md gap-2" role="search">
                <label for="nq" class="sr-only">Search naats</label>
                <input id="nq" type="search" name="q" value="{{ $q }}" placeholder="Search by title, poet or reciter" class="w-full rounded-full border-slate-300 px-4 py-2">
                <button class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white">Search</button>
            </form>
        @endif
        <div class="mt-6 grid gap-3 sm:grid-cols-2">
            @forelse ($naats as $n)
                <article class="flex flex-col rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h2 class="font-semibold"><a href="{{ route('naats.show', $n) }}" class="hover:underline">{{ $n->title }}</a></h2>
                    @if ($n->title_ur)<p class="font-urdu mt-1 text-right text-xl" dir="rtl" lang="ur">{{ $n->title_ur }}</p>@endif
                    <p class="mt-1 text-xs text-slate-500">@if ($n->poet)Poet: {{ $n->poet }}@endif @if ($n->reciter) · Recited by {{ $n->reciter }}@endif</p>
                    @if ($n->audio)
                        <button type="button" @click="BFPlayer.load([{ src: {{ Js::from($n->audio->src()) }}, title: {{ Js::from($n->title) }}, subtitle: {{ Js::from($n->reciter ?: 'Naat') }}, key: 'naat:{{ $n->id }}' }], 0)"
                                class="mt-3 inline-flex w-fit items-center gap-1.5 rounded-full bg-emerald-700 px-4 py-2 text-sm font-semibold text-white" aria-label="Play {{ $n->title }}">▶ Play</button>
                    @endif
                </article>
            @empty
                <p class="text-slate-500 sm:col-span-2">{{ $q !== '' ? 'No naats match your search.' : 'Our editors are adding naats with properly licensed recordings. Please check back soon.' }}</p>
            @endforelse
        </div>
        <x-explore group="islamic" />
    </div>
</x-layout>
