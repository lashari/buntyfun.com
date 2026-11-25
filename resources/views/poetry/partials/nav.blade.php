<nav class="mt-5 flex flex-wrap gap-2 text-sm font-semibold" aria-label="Poetry categories">
    @foreach (\App\Models\Poem::FORMS as $k => $l)
        <a href="{{ route('poetry.segment', $k) }}" class="rounded-full px-3 py-1 {{ ($active ?? '') === $k ? 'bg-navy-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ $l[0] }}</a>
    @endforeach
    @foreach (\App\Models\Poem::MOODS as $k => $l)
        <a href="{{ route('poetry.segment', $k) }}" class="rounded-full px-3 py-1 {{ ($active ?? '') === $k ? 'bg-brand-600 text-white' : 'bg-brand-50 text-brand-800 hover:bg-brand-100' }}">{{ str_replace(' poetry', '', $l[0]) }}</a>
    @endforeach
</nav>
