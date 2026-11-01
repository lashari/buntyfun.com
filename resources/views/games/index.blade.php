<x-layout title="Games — BuntyFun" description="Free browser games — no install, no sign-up.">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">Games</h1>
        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            @forelse ($games as $game)
                <a href="{{ route('games.show', $game) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                    <h2 class="font-semibold">{{ $game->title }}</h2>
                </a>
            @empty
                <p class="text-sm text-slate-500">No games published yet.</p>
            @endforelse
        </div>
    </div>
</x-layout>
