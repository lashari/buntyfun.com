<x-layout :title="$game->title.' — Check Your WPM Free Online | BuntyFun'" :breadcrumbs="['Games' => route('games.index'), $game->title => null]" :description="\App\Content\GameGuides::for($game->slug)['summary'].' Free, no sign-up.'">
<style>#typing [aria-pressed="true"] { background: #1e3a5f; color: #fff; }</style>
<div id="typing" class="mx-auto max-w-2xl px-4 py-8 text-center">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ $game->title }}</h1>
    <div class="mt-3 flex flex-wrap justify-center gap-2 text-sm font-semibold">
        <button type="button" data-level="easy" class="rounded-full bg-slate-100 px-3 py-1">Easy</button>
        <button type="button" data-level="medium" class="rounded-full bg-slate-100 px-3 py-1">Medium</button>
        <button type="button" data-level="hard" class="rounded-full bg-slate-100 px-3 py-1">Hard</button>
        <span class="px-1 text-slate-300">|</span>
        <button type="button" data-dur="30" class="rounded-full bg-slate-100 px-3 py-1">30 s</button>
        <button type="button" data-dur="60" class="rounded-full bg-slate-100 px-3 py-1">60 s</button>
    </div>
    <p data-live class="mt-4 text-lg font-bold" aria-live="off"></p>
    <div data-text class="mt-3 rounded-xl border border-slate-200 bg-white p-4 text-left font-mono text-lg leading-relaxed"></div>
    <textarea data-input rows="3" class="mt-3 w-full rounded-xl border-2 border-slate-300 p-3 font-mono text-lg focus:border-brand-600" placeholder="Start typing here — the timer starts with your first key" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" aria-label="Type the text above"></textarea>
    <div data-end hidden class="mt-4 rounded-2xl bg-emerald-50 p-4">
        <p data-final class="text-lg font-bold"></p>
        <button type="button" data-share class="mt-3 rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white">Share</button>
    </div>
    <button type="button" data-restart class="mt-4 rounded-full bg-brand-600 px-5 py-2 font-semibold text-white">Restart</button>
    <p data-best class="mt-3 text-sm text-slate-500"></p>
</div>
<script src="/js/games/typing-test.js?v=1" defer></script>
@include('games.partials.guide')
</x-layout>
