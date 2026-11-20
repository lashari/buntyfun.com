<x-layout title="Free Browser Games — No Download, No Sign-up | BuntyFun" description="Play Tic-Tac-Toe, Memory Match, Word Scramble, Rock Paper Scissors and a daily riddle in your browser, with rules and tips for each game.">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">Games</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">Quick games for a short break. They run in your browser on a phone or computer, need no download or account, and each page explains the rules, how scoring works and a few tips.</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($games as $game)
                <a href="{{ route('games.show', $game) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                    <h2 class="font-semibold">{{ $game->title }}</h2>
                    @if ($g = \App\Content\GameGuides::for($game->slug))
                        <p class="mt-1 text-sm text-slate-600">{{ $g['summary'] }}</p>
                    @endif
                </a>
            @empty
                <p class="text-sm text-slate-500">No games published yet.</p>
            @endforelse
        </div>
    </div>
</x-layout>
