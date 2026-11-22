<x-layout :title="$wallpaper->title.' — Wallpaper — BuntyFun'" :description="'Download '.$wallpaper->title.' free, '.$wallpaper->resolutionLabel().'.'"
          :meta="$wallpaper->meta" :og-image="$wallpaper->previewUrl()" :breadcrumbs="['Wallpapers' => route('wallpapers.index'), Str::headline($wallpaper->category) => route('wallpapers.category', $wallpaper->category), $wallpaper->title => null]">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <div class="relative">
            <img src="{{ $wallpaper->previewUrl() }}" alt="{{ $wallpaper->title }}" class="w-full rounded-xl border border-slate-200">
            @if ($previous)
                <a href="{{ route('wallpapers.show', $previous) }}" rel="prev" aria-label="Back: {{ $previous->title }}" title="Back"
                   class="absolute left-3 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-black/50 text-2xl font-bold text-white shadow-lg backdrop-blur hover:bg-black/70">&#8249;</a>
            @endif
            @if ($next)
                <a href="{{ route('wallpapers.show', $next) }}" rel="next" aria-label="Next: {{ $next->title }}" title="Next"
                   class="absolute right-3 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-black/50 text-2xl font-bold text-white shadow-lg backdrop-blur hover:bg-black/70">&#8250;</a>
            @endif
            <a href="{{ route('wallpapers.category', $wallpaper->category) }}"
               class="absolute bottom-3 left-1/2 -translate-x-1/2 rounded-full bg-black/50 px-3 py-1 text-xs text-white backdrop-blur hover:bg-black/70">
                {{ $position }} of {{ $total }} in {{ Str::headline($wallpaper->category) }}
            </a>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">{{ $wallpaper->title }}</h1>
                <p class="text-sm text-slate-500">{{ Str::headline($wallpaper->category) }} · {{ $wallpaper->resolutionLabel() }} · {{ Str::headline($wallpaper->orientation) }}</p>
            </div>
            <a href="{{ route('wallpapers.download', $wallpaper) }}" class="rounded-full bg-brand-600 px-5 py-2 font-semibold text-white hover:bg-brand-700">
                Download
            </a>
        </div>

        @if ($wallpaper->attribution || $wallpaper->source_url)
            <p class="mt-6 text-xs text-slate-500">
                @if ($wallpaper->attribution && $wallpaper->attribution_url)
                    <a href="{{ $wallpaper->attribution_url }}" class="hover:underline" rel="nofollow noopener" target="_blank">{{ $wallpaper->attribution }}</a>
                @elseif ($wallpaper->attribution) {{ $wallpaper->attribution }} @endif
                @if ($wallpaper->source_url) · <a href="{{ $wallpaper->source_url }}" class="hover:underline" rel="nofollow noopener" target="_blank">Source</a> @endif
            </p>
        @endif

        <a href="{{ route('wallpapers.index') }}" class="mt-6 block text-center text-sm text-slate-500 hover:underline">All wallpaper categories</a>
    </div>
</x-layout>
