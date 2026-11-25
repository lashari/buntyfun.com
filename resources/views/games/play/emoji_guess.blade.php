<x-layout :title="$game->title.' — Guess the Word from Emojis, Free | BuntyFun'" :breadcrumbs="['Games' => route('games.index'), $game->title => null]" :description="\App\Content\GameGuides::for($game->slug)['summary'].' Free, no sign-up, works on phones.'">
<style>#emoji [aria-pressed="true"] { background: #1e3a5f; color: #fff; }</style>
<div id="emoji" class="mx-auto max-w-md px-4 py-8 text-center">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ $game->title }}</h1>
    <div class="mt-3 flex flex-wrap justify-center gap-2 text-sm font-semibold">
        @foreach (['Food', 'Sayings', 'Places', 'Jobs', 'Things', 'Mix'] as $c)
            <button type="button" data-cat="{{ $c }}" class="rounded-full bg-slate-100 px-3 py-1">{{ $c }}</button>
        @endforeach
    </div>
    <div data-play class="mt-5">
        <p data-count class="text-sm text-slate-600"></p>
        <p data-emoji class="mt-4 text-6xl leading-tight" aria-live="polite"></p>
        <div data-opts class="mt-6 grid gap-2"></div>
        <button type="button" data-nextq hidden class="mt-4 rounded-full bg-brand-600 px-6 py-2 font-semibold text-white">Next</button>
    </div>
    <div data-end hidden class="mt-6 rounded-2xl bg-emerald-50 p-5">
        <p data-final class="text-lg font-bold"></p>
        <div class="mt-3 flex justify-center gap-2">
            <button type="button" data-again class="rounded-full bg-brand-600 px-5 py-2 font-semibold text-white">Play again</button>
            <button type="button" data-share class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white">Share</button>
        </div>
    </div>
</div>
<script src="/js/games/emoji-guess.js?v=1" defer></script>
@include('games.partials.guide')
</x-layout>
