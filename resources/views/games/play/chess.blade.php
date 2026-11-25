<x-layout :title="$game->title.' — Play Free Online vs Computer or a Friend | BuntyFun'" :breadcrumbs="['Games' => route('games.index'), $game->title => null]" :description="\App\Content\GameGuides::for($game->slug)['summary'].' Free, no sign-up, works on phones.'">
<style>
    #chess .cb { display: grid; grid-template-columns: repeat(8, 1fr); width: min(92vw, 520px); aspect-ratio: 1; margin: 0 auto; border: 4px solid #334155; border-radius: 6px; overflow: hidden; touch-action: manipulation; }
    #chess .cq { position: relative; display: flex; align-items: center; justify-content: center; font-size: min(9vw, 46px); line-height: 1; user-select: none; }
    #chess .cq-l { background: #f1e6cf; } #chess .cq-d { background: #a97a55; }
    #chess .cq-last { box-shadow: inset 0 0 0 100px rgba(250, 204, 21, .35); }
    #chess .cq-sel { box-shadow: inset 0 0 0 100px rgba(34, 197, 94, .45); }
    #chess .cq-check { box-shadow: inset 0 0 0 100px rgba(239, 68, 68, .6); }
    #chess .pw { color: #fff; text-shadow: 0 0 2px #000, 0 0 2px #000, 0 1px 2px #000; }
    #chess .pb { color: #111827; text-shadow: 0 0 1px #fff; }
    #chess .cq-dot { position: absolute; width: 28%; height: 28%; border-radius: 50%; background: rgba(15, 23, 42, .35); }
    #chess .cq-cap { position: absolute; inset: 4%; border-radius: 50%; border: 4px solid rgba(15, 23, 42, .4); }
    #chess [aria-pressed="true"] { background: #1e3a5f; color: #fff; }
</style>
<div id="chess" class="mx-auto max-w-2xl px-4 py-8 text-center">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ $game->title }}</h1>
    <div class="mt-3 flex flex-wrap justify-center gap-2 text-sm font-semibold">
        <button type="button" data-mode="cpu" aria-pressed="true" class="rounded-full bg-slate-100 px-3 py-1">vs Computer</button>
        <button type="button" data-mode="2p" aria-pressed="false" class="rounded-full bg-slate-100 px-3 py-1">2 Players</button>
    </div>
    <div data-levels class="mt-2 flex flex-wrap justify-center gap-2 text-sm font-semibold">
        <button type="button" data-level="1" aria-pressed="false" class="rounded-full bg-slate-100 px-3 py-1">Easy</button>
        <button type="button" data-level="2" aria-pressed="true" class="rounded-full bg-slate-100 px-3 py-1">Medium</button>
        <button type="button" data-level="3" aria-pressed="false" class="rounded-full bg-slate-100 px-3 py-1">Hard</button>
    </div>
    <p data-status class="mt-3 font-semibold text-slate-700" aria-live="polite"></p>
    <div data-board class="cb mt-3"></div>
    <div data-result hidden class="mx-auto mt-4 max-w-md rounded-2xl bg-emerald-50 p-4">
        <p data-result-text class="text-lg font-bold"></p>
        <div class="mt-3 flex justify-center gap-2">
            <button type="button" data-again class="rounded-full bg-brand-600 px-5 py-2 font-semibold text-white">Play again</button>
            <button type="button" data-share class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white">Share</button>
        </div>
    </div>
    <div class="mt-4 flex flex-wrap justify-center gap-2 text-sm font-semibold">
        <button type="button" data-new class="rounded-full bg-brand-600 px-4 py-2 text-white">New game</button>
        <button type="button" data-undo class="rounded-full bg-slate-200 px-4 py-2">Undo</button>
        <button type="button" data-flip class="rounded-full bg-slate-200 px-4 py-2">Flip board</button>
    </div>
    <p data-wins class="mt-3 text-xs text-slate-500"></p>
    <p data-moves class="mx-auto mt-3 max-w-md break-words rounded-lg bg-slate-50 p-3 text-left font-mono text-xs text-slate-600"></p>
</div>
<script src="/js/games/chess.js?v=1" defer></script>
@include('games.partials.guide')
</x-layout>
