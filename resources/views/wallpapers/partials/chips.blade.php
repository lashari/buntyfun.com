<nav class="mt-4 flex flex-wrap gap-2" aria-label="Wallpaper categories">
    <a href="{{ route('wallpapers.index') }}"
       class="rounded-full border px-4 py-1.5 text-sm font-medium {{ $active === null ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-brand-600' }}">
        All <span class="opacity-70">({{ $counts->sum() }})</span>
    </a>
    @foreach ($counts as $name => $total)
        <a href="{{ route('wallpapers.category', $name) }}"
           class="rounded-full border px-4 py-1.5 text-sm font-medium {{ $active === $name ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-brand-600' }}">
            {{ Str::headline($name) }} <span class="opacity-70">({{ $total }})</span>
        </a>
    @endforeach
</nav>
