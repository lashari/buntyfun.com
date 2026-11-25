<nav class="mt-5 flex flex-wrap gap-2 text-sm font-semibold" aria-label="Name lists">
    <a href="{{ route('name-entries.index') }}" class="rounded-full px-3 py-1 {{ ($active ?? null) === null ? 'bg-navy-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">All names</a>
    @foreach (\App\Models\NameEntry::LISTS as $key => $l)
        <a href="{{ route('name-entries.list', $key) }}" class="rounded-full px-3 py-1 {{ ($active ?? null) === $key ? 'bg-navy-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ str_replace(' (Companions)', '', $l['title']) }}</a>
    @endforeach
    <a href="{{ route('islamic.allah-names') }}" class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-800 hover:bg-emerald-100">99 Names of Allah</a>
</nav>
