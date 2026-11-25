@php($label = Str::headline($category))
<x-layout :title="$label.' Wallpapers HD — Free Download'.($wallpapers->currentPage() > 1 ? ' (Page '.$wallpapers->currentPage().')' : '').' — BuntyFun'"
          :description="'Free '.$label.' wallpapers in HD for desktop and mobile. '.$wallpapers->total().' pictures, free download.'"
          :schema="\App\Support\Schema::itemList($label.' Wallpapers', $wallpapers->map(fn ($w) => [route('wallpapers.show', $w), $w->title]), $wallpapers->firstItem() ?? 1)"
          :breadcrumbs="['Wallpapers' => route('wallpapers.index'), $label => null]">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ $label }} Wallpapers</h1>
        @include('wallpapers.partials.chips', ['active' => $category])

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($wallpapers as $wallpaper)
                @include('wallpapers.partials.card')
            @endforeach
        </div>

        @if ($wallpapers->hasPages())
            <div class="mt-8 flex items-center justify-between">
                @if ($wallpapers->onFirstPage())
                    <span class="rounded-full border border-slate-200 px-5 py-2 text-sm text-slate-400">← Back</span>
                @else
                    <a href="{{ $wallpapers->currentPage() === 2 ? route('wallpapers.category', $category) : $wallpapers->previousPageUrl() }}" rel="prev" class="rounded-full border border-slate-300 bg-white px-5 py-2 text-sm font-semibold hover:border-brand-600">← Back</a>
                @endif
                {{-- Numbered links so every page (and every wallpaper on it) is one click from page 1. --}}
                <nav aria-label="Pages" class="flex flex-wrap justify-center gap-1 text-sm">
                    @foreach (range(1, $wallpapers->lastPage()) as $n)
                        @if ($n === $wallpapers->currentPage())
                            <span aria-current="page" class="rounded-full bg-navy-700 px-3 py-1.5 font-semibold text-white">{{ $n }}</span>
                        @else
                            <a href="{{ $n === 1 ? route('wallpapers.category', $category) : $wallpapers->url($n) }}" class="rounded-full border border-slate-300 bg-white px-3 py-1.5 hover:border-brand-600">{{ $n }}</a>
                        @endif
                    @endforeach
                </nav>
                @if ($wallpapers->hasMorePages())
                    <a href="{{ $wallpapers->nextPageUrl() }}" rel="next" class="rounded-full bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">Next →</a>
                @else
                    <span class="rounded-full border border-slate-200 px-5 py-2 text-sm text-slate-400">Next →</span>
                @endif
            </div>
        @endif
    </div>
</x-layout>
