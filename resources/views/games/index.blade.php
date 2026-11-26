<x-layout :breadcrumbs="['Games' => null]" :title="$for ? 'Free Games for '.\App\Content\OssGames::AUDIENCES[$for].' — No Download | BuntyFun' : 'Free Browser Games — No Download, No Sign-up | BuntyFun'" description="Play free browser games: puzzles like 2048, arcade, pool, piano, Tic-Tac-Toe, Memory Match and a daily riddle. Filter games for kids, teens, adults or the whole family." :noindex="(bool) $for" :schema="$for ? [] : \App\Support\Schema::itemList('Free browser games', $games->map(fn ($g) => [route('games.show', $g), $g->title]))">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">Games</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">Quick games for a short break. They run in your browser on a phone or computer, need no download or account, and each page explains the rules, how scoring works and a few tips.</p>
        <nav class="mt-5 flex flex-wrap gap-2 text-sm" aria-label="Filter games by age">
            <a href="{{ route('games.index') }}" class="rounded-full px-3 py-1 {{ $for ? 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50' : 'bg-brand-600 font-semibold text-white' }}">All games</a>
            @foreach (\App\Content\OssGames::AUDIENCES as $key => $label)
                <a href="{{ route('games.index', ['for' => $key]) }}" class="rounded-full px-3 py-1 {{ $for === $key ? 'bg-brand-600 font-semibold text-white' : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}">{{ $label }}</a>
            @endforeach
        </nav>
        <x-my-games type="game" class="mt-6" />
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($games as $game)
                <a href="{{ route('games.show', $game) }}" class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    @if ($thumb = \App\Content\OssGames::thumb($game->slug))
                        <img src="{{ $thumb }}" alt="{{ $game->title }} game preview" width="400" height="250" loading="lazy" class="aspect-[8/5] w-full object-cover transition group-hover:scale-[1.02]">
                    @endif
                    <div class="p-5">
                    <h2 class="font-semibold">{{ $game->title }}</h2>
                    @if ($g = \App\Content\GameGuides::for($game->slug))
                        <p class="mt-1 text-sm text-slate-600">{{ $g['summary'] }}</p>
                    @endif
                    <p class="mt-2 flex flex-wrap gap-1 text-xs">
                        @if (! empty($g['genre']))<span class="rounded-full bg-sea-50 px-2 py-0.5 text-sea-800">{{ $g['genre'] }}</span>@endif
                        @foreach (\App\Content\OssGames::audienceOf($game) as $a)
                            <span class="rounded-full bg-brand-50 px-2 py-0.5 text-brand-700">{{ \App\Content\OssGames::AUDIENCES[$a] }}</span>
                        @endforeach
                    </p>
                    </div>
                </a>
            @empty
                <p class="text-sm text-slate-500">No games in this group yet.</p>
            @endforelse
        </div>
        <x-explore group="games" />
    </div>
</x-layout>
