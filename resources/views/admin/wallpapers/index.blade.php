<x-admin-layout title="Wallpapers">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Wallpapers</h1>
        <a href="{{ route('admin.wallpapers.create') }}" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">Upload Wallpaper</a>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($wallpapers as $wallpaper)
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <img src="{{ $wallpaper->url() }}" alt="{{ $wallpaper->title }}" class="aspect-video w-full object-cover">
                <div class="p-3">
                    <p class="font-medium">{{ $wallpaper->title }}</p>
                    <p class="text-xs text-slate-500">{{ Str::headline($wallpaper->category) }} · {{ $wallpaper->resolutionLabel() }} · {{ Str::headline($wallpaper->license) }}</p>
                    <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs {{ $wallpaper->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ Str::headline($wallpaper->status) }}
                    </span>
                    <div class="mt-2 flex items-center gap-3 text-sm">
                        <a href="{{ route('admin.wallpapers.edit', $wallpaper) }}" class="text-violet-600 hover:underline">Edit</a>
                        @if (auth()->user()->canPublish())
                            @if ($wallpaper->status === 'published')
                                <form method="POST" action="{{ route('admin.wallpapers.unpublish', $wallpaper) }}">
                                    @csrf
                                    <button type="submit" class="text-slate-500 hover:underline">Unpublish</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.wallpapers.publish', $wallpaper) }}">
                                    @csrf
                                    <button type="submit" class="text-emerald-600 hover:underline">Publish</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">No wallpapers uploaded yet.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $wallpapers->links() }}</div>
</x-admin-layout>
