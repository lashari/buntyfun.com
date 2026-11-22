<a href="{{ route('wallpapers.show', $wallpaper) }}" class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm hover:shadow-md">
    <img src="{{ $wallpaper->thumbUrl() }}" alt="{{ $wallpaper->title }}" loading="lazy"
         class="aspect-video w-full object-cover transition group-hover:scale-105">
    <div class="p-3">
        <p class="truncate font-medium">{{ $wallpaper->title }}</p>
        <p class="text-xs text-slate-500">{{ Str::headline($wallpaper->category) }} · {{ $wallpaper->resolutionLabel() }}</p>
    </div>
</a>
