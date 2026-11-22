@php($label = Str::headline($category))
<x-layout :title="$label.' Wallpapers HD — Free Download'.($wallpapers->currentPage() > 1 ? ' (Page '.$wallpapers->currentPage().')' : '').' — BuntyFun'"
          :description="'Free '.$label.' wallpapers in HD for desktop and mobile. '.$wallpapers->total().' pictures, free download.'"
          :noindex="$wallpapers->currentPage() > 1"
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
                    <a href="{{ $wallpapers->previousPageUrl() }}" rel="prev" class="rounded-full border border-slate-300 bg-white px-5 py-2 text-sm font-semibold hover:border-brand-600">← Back</a>
                @endif
                <span class="text-sm text-slate-500">Page {{ $wallpapers->currentPage() }} of {{ $wallpapers->lastPage() }}</span>
                @if ($wallpapers->hasMorePages())
                    <a href="{{ $wallpapers->nextPageUrl() }}" rel="next" class="rounded-full bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">Next →</a>
                @else
                    <span class="rounded-full border border-slate-200 px-5 py-2 text-sm text-slate-400">Next →</span>
                @endif
            </div>
        @endif
    </div>
</x-layout>
