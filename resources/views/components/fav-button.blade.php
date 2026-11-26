@props(['title', 'url', 'type' => 'game'])
{{-- Save to favourites (this browser only). Also remembers the page as recently opened. --}}
@php $item = ['url' => parse_url($url, PHP_URL_PATH) ?: '/', 'title' => $title, 'type' => $type]; @endphp
<div x-data="{ item: {{ Js::from($item) }}, on: false }"
     x-init="if (window.BF) { BF.visited(item); on = BF.isFav(item.url); }"
     {{ $attributes->merge(['class' => 'flex justify-center']) }}>
    <button type="button" @click="on = BF.toggleFav(item)" :aria-pressed="on.toString()"
            class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-sm font-medium transition"
            :class="on ? 'border-sun-400 bg-sun-50 text-navy-700' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'">
        <span aria-hidden="true" x-text="on ? '★' : '☆'">☆</span>
        <span x-text="on ? 'In your favourites' : 'Add to favourites'">Add to favourites</span>
    </button>
</div>
