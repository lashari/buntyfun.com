{{-- "Explore <section>" links for hub and detail pages, from the same list as the header (App\Content\SiteNav). --}}
@props(['group', 'title' => null, 'extra' => []])
@php
    $g = \App\Content\SiteNav::group($group);
    $links = $g ? collect($g['links'])->reject(fn ($l) => $l[0] === url()->current())->concat($extra)->unique(fn ($l) => $l[0])->values()->all() : [];
@endphp
@if ($links)
    <section {{ $attributes->merge(['class' => 'mt-12']) }} dir="ltr" aria-label="{{ $title ?? 'Explore '.$g['label'] }}">
        <h2 class="text-lg font-bold">{{ $title ?? 'Explore '.$g['label'] }}</h2>
        <x-link-chips class="mt-3" :links="$links" />
    </section>
@endif
