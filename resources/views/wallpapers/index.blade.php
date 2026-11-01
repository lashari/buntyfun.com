<x-layout title="Wallpapers — BuntyFun" description="Free wallpapers for desktop and mobile, by category.">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">Wallpapers</h1>

        <form method="GET" class="mt-4 flex flex-wrap gap-3">
            <select name="category" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ Str::headline($category) }}</option>
                @endforeach
            </select>
        </form>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($wallpapers as $wallpaper)
                <a href="{{ route('wallpapers.show', $wallpaper) }}" class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm hover:shadow-md">
                    <img src="{{ $wallpaper->url() }}" alt="{{ $wallpaper->title }}" loading="lazy"
                         class="aspect-video w-full object-cover transition group-hover:scale-105">
                    <div class="p-3">
                        <p class="font-medium">{{ $wallpaper->title }}</p>
                        <p class="text-xs text-slate-400">{{ Str::headline($wallpaper->category) }} · {{ $wallpaper->resolutionLabel() }}</p>
                    </div>
                </a>
            @empty
                <p class="text-sm text-slate-500">No wallpapers published yet.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $wallpapers->links() }}</div>
    </div>
</x-layout>
