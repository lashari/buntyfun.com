<nav class="mt-6 flex flex-wrap gap-2 text-sm" aria-label="Dua categories">
    <a href="{{ route('duas.index') }}" class="rounded-full border px-3 py-1.5 font-semibold {{ empty($active) ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-emerald-600' }}">All</a>
    @foreach (\App\Models\Dua::CATEGORY_SLUGS as $c)
        @if (($counts[$c] ?? 0) > 0)
            <a href="{{ route('duas.category', $c) }}" class="rounded-full border px-3 py-1.5 font-semibold {{ ($active ?? null) === $c ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-emerald-600' }}">{{ \App\Models\Dua::CATEGORIES[$c] }} <span class="opacity-70">({{ $counts[$c] }})</span></a>
        @endif
    @endforeach
</nav>
