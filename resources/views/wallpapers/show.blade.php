<x-layout :title="$wallpaper->title.' — Wallpaper — BuntyFun'" :description="'Download '.$wallpaper->title.' free, '.$wallpaper->resolutionLabel().'.'">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <img src="{{ $wallpaper->url() }}" alt="{{ $wallpaper->title }}" class="w-full rounded-xl border border-slate-200">

        <div class="mt-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">{{ $wallpaper->title }}</h1>
                <p class="text-sm text-slate-500">{{ Str::headline($wallpaper->category) }} · {{ $wallpaper->resolutionLabel() }} · {{ Str::headline($wallpaper->orientation) }}</p>
            </div>
            <a href="{{ route('wallpapers.download', $wallpaper) }}" class="rounded-full bg-violet-600 px-5 py-2 font-semibold text-white hover:bg-violet-700">
                Download
            </a>
        </div>

        @if ($wallpaper->attribution || $wallpaper->source_url)
            <p class="mt-6 text-xs text-slate-500">
                @if ($wallpaper->attribution) {{ $wallpaper->attribution }} @endif
                @if ($wallpaper->source_url) · <a href="{{ $wallpaper->source_url }}" class="hover:underline" rel="nofollow noopener" target="_blank">Source</a> @endif
            </p>
        @endif

        <a href="{{ route('wallpapers.index') }}" class="mt-8 block text-sm text-slate-500 hover:underline">More wallpapers</a>
    </div>
</x-layout>
