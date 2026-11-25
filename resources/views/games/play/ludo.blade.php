<x-layout :title="$game->title.' — Play Free vs Computer or Friends | BuntyFun'" :breadcrumbs="['Games' => route('games.index'), $game->title => null]" :description="\App\Content\GameGuides::for($game->slug)['summary'].' Free, no sign-up, works on phones.'">
<style>
    #ludo .lu-b { display: grid; grid-template-columns: repeat(15, 1fr); width: min(94vw, 540px); aspect-ratio: 1; margin: 0 auto; border: 3px solid #334155; border-radius: 8px; overflow: hidden; background: #fff; }
    #ludo .lu-c { position: relative; display: flex; align-items: center; justify-content: center; }
    #ludo .lu-star { color: #94a3b8; font-size: min(3.4vw, 18px); }
    #ludo .lu-toks { position: absolute; inset: 6%; display: grid; gap: 1px; }
    #ludo .lu-n1 { grid-template-columns: 1fr; } #ludo .lu-n2, #ludo .lu-n3, #ludo .lu-n4 { grid-template-columns: 1fr 1fr; }
    #ludo .lu-tok { border-radius: 50%; border: 2px solid #fff; box-shadow: 0 1px 2px rgba(0,0,0,.5); width: 100%; height: 100%; min-height: 0; }
    #ludo .lu-glow { animation: lu-p 0.8s infinite alternate; border-color: #111827; }
    @keyframes lu-p { from { transform: scale(.85); } to { transform: scale(1.1); } }
    #ludo [aria-pressed="true"] { background: #1e3a5f; color: #fff; }
</style>
<div id="ludo" class="mx-auto max-w-2xl px-3 py-8 text-center">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ $game->title }}</h1>
    <div class="mt-3 flex flex-wrap justify-center gap-2 text-sm font-semibold">
        <button type="button" data-n="2" aria-pressed="false" class="rounded-full bg-slate-100 px-3 py-1">2 players</button>
        <button type="button" data-n="3" aria-pressed="false" class="rounded-full bg-slate-100 px-3 py-1">3 players</button>
        <button type="button" data-n="4" aria-pressed="true" class="rounded-full bg-slate-100 px-3 py-1">4 players</button>
        <span class="px-1 text-slate-300">|</span>
        <button type="button" data-vs="cpu" aria-pressed="true" class="rounded-full bg-slate-100 px-3 py-1">You vs computer</button>
        <button type="button" data-vs="friends" aria-pressed="false" class="rounded-full bg-slate-100 px-3 py-1">Pass &amp; play</button>
    </div>
    <p data-msg class="mt-3 min-h-6 text-sm text-slate-700" aria-live="polite"></p>
    <div data-board class="lu-b mt-3"></div>
    <div class="mt-4 flex items-center justify-center gap-4">
        <span data-dice class="text-6xl leading-none" aria-live="polite">🎲</span>
        <button type="button" data-roll class="rounded-full px-8 py-3 text-lg font-bold text-white disabled:opacity-40">Roll</button>
    </div>
    <div data-end hidden class="mx-auto mt-4 max-w-md rounded-2xl bg-emerald-50 p-4">
        <p data-final class="text-lg font-bold"></p>
        <div class="mt-3 flex justify-center gap-2">
            <button type="button" data-again class="rounded-full bg-brand-600 px-5 py-2 font-semibold text-white">Play again</button>
            <button type="button" data-share class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white">Share</button>
        </div>
    </div>
    <button type="button" data-new class="mt-4 rounded-full bg-slate-200 px-4 py-2 text-sm font-semibold">New game</button>
    <p data-wins class="mt-2 text-xs text-slate-500"></p>
</div>
<script src="/js/games/ludo.js?v=1" defer></script>
@include('games.partials.guide')
</x-layout>
