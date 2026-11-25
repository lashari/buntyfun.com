<x-layout :title="$game->title.' — Guess Today\'s 5-Letter Word Free | BuntyFun'" :breadcrumbs="['Games' => route('games.index'), $game->title => null]" :description="\App\Content\GameGuides::for($game->slug)['summary'].' Free, no sign-up, works on phones.'">
<style>
    #dword .dw-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; width: min(86vw, 320px); margin: 0 auto; }
    #dword .dw-cell { aspect-ratio: 1; border: 2px solid #cbd5e1; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; text-transform: uppercase; }
    #dword .dw-row { display: flex; justify-content: center; gap: 5px; margin-top: 6px; }
    #dword .dw-key { flex: 1; max-width: 42px; height: 52px; border-radius: 6px; background: #e2e8f0; font-weight: 700; font-size: .95rem; }
    #dword .dw-wide { max-width: 66px; flex: 1.6; font-size: .75rem; }
    #dword [aria-pressed="true"] { background: #1e3a5f; color: #fff; }
</style>
<div id="dword" class="mx-auto max-w-md px-3 py-8 text-center">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ $game->title }}</h1>
    <div class="mt-3 flex justify-center gap-2 text-sm font-semibold">
        <button type="button" data-m="daily" class="rounded-full bg-slate-100 px-3 py-1">Today's word</button>
        <button type="button" data-m="practice" class="rounded-full bg-slate-100 px-3 py-1">Practice</button>
    </div>
    <p data-msg class="mt-3 min-h-6 text-sm font-semibold text-slate-700" aria-live="polite"></p>
    <div data-grid class="dw-grid mt-3"></div>
    <div data-end hidden class="mt-4 flex justify-center gap-2">
        <button type="button" data-share class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white">Share result</button>
        <button type="button" data-next class="rounded-full bg-brand-600 px-5 py-2 font-semibold text-white">Practice word</button>
    </div>
    <div data-kb class="mt-5"></div>
    <p data-stats class="mt-4 text-xs text-slate-500"></p>
    <p class="mt-2 text-xs text-slate-500">🟩 right letter, right place · 🟨 in the word, wrong place · ⬜ not in the word</p>
</div>
<script src="/js/games/daily-word.js?v=1" defer></script>
@include('games.partials.guide')
</x-layout>
