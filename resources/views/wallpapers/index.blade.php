<x-layout title="Free HD Wallpapers by Category — BuntyFun" description="Free HD wallpapers for desktop and mobile: space, nature, Islamic, cars, animals and more. Browse by category and download."
          :noindex="$counts->isEmpty()" :breadcrumbs="['Wallpapers' => null]">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">Wallpapers</h1>
        @include('wallpapers.partials.chips', ['active' => null])

        @forelse ($sections as $category => $items)
            <section class="mt-10">
                <div class="flex items-baseline justify-between">
                    <h2 class="text-xl font-bold">{{ Str::headline($category) }} <span class="text-sm font-normal text-slate-500">{{ $counts[$category] }} wallpapers</span></h2>
                    <a href="{{ route('wallpapers.category', $category) }}" class="text-sm font-semibold text-brand-600 hover:underline">View all →</a>
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $wallpaper)
                        @include('wallpapers.partials.card')
                    @endforeach
                </div>
            </section>
        @empty
            <p class="mt-6 text-sm text-slate-500">No wallpapers published yet.</p>
        @endforelse
    </div>
</x-layout>
