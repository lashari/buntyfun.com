<x-layout title="Islamic Names with Meanings — Arabic & Urdu Spelling | BuntyFun" description="Muslim baby names for boys and girls with Arabic and Urdu spelling, meaning and a cited source for every name."
          :noindex="request()->hasAny(['q', 'gender', 'letter', 'page'])">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">Islamic Names</h1>
        <div class="mt-3 max-w-3xl space-y-3 leading-relaxed text-slate-700">
            <p>Muslim names for boys and girls, each with its Arabic spelling, Urdu spelling, gender and meaning. Every meaning is checked against a published reference, and the source is linked on the name's page. Where references disagree about a name's origin, we say so rather than choose one.</p>
            <p>Many names come from Arabic roots of three letters that carry a core idea. Muhammad, Ahmad and Hamid all share the root ḥ-m-d ("praise"), for example. English spellings of the same name differ widely, so search by the spelling you know and check the Arabic form on the name page.</p>
        </div>

        <form method="GET" class="mt-4 flex flex-wrap gap-3">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name or meaning"
                   class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select name="gender" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">All genders</option>
                <option value="male" @selected(request('gender') === 'male')>Boys</option>
                <option value="female" @selected(request('gender') === 'female')>Girls</option>
                <option value="unisex" @selected(request('gender') === 'unisex')>Unisex</option>
            </select>
            <button type="submit" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700">Search</button>
        </form>

        <div class="mt-4 flex flex-wrap gap-1 text-sm">
            @foreach (range('A', 'Z') as $letter)
                <a href="{{ route('name-entries.index', array_merge(request()->except('letter', 'page'), ['letter' => $letter])) }}"
                   class="rounded px-2 py-1 {{ request('letter') === $letter ? 'bg-violet-600 text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                    {{ $letter }}
                </a>
            @endforeach
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            @forelse ($names as $name)
                <a href="{{ route('name-entries.show', $name) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                    <h2 class="font-semibold">{{ $name->name_en }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ Str::limit($name->meaning, 60) }}</p>
                </a>
            @empty
                <p class="text-sm text-slate-500">No names match this search yet.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $names->links() }}</div>
    </div>
</x-layout>
