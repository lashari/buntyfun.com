<x-layout title="Islamic Names — BuntyFun" description="Islamic and Muslim baby names with meanings, searchable by letter and gender.">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">Islamic Names</h1>

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
