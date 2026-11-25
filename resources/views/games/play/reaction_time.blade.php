<x-layout :title="$game->title.' — Test How Fast You React, Free | BuntyFun'" :breadcrumbs="['Games' => route('games.index'), $game->title => null]" :description="\App\Content\GameGuides::for($game->slug)['summary'].' Free, no sign-up, works on phones.'">
<div id="react" class="mx-auto max-w-xl px-4 py-8 text-center">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ $game->title }}</h1>
    <button type="button" data-pad class="mt-5 flex h-72 w-full select-none flex-col items-center justify-center rounded-3xl text-white shadow-lg sm:h-80" style="touch-action: manipulation">
        <span data-t class="text-4xl font-extrabold"></span>
        <span data-s class="mt-2 px-4 text-base opacity-90"></span>
    </button>
    <p data-list class="mt-4 text-sm text-slate-600"></p>
    <button type="button" data-share hidden class="mt-3 rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white">Share</button>
    <p class="mt-3 text-xs text-slate-500">On a computer you can press Space instead of clicking.</p>
</div>
<script src="/js/games/reaction-time.js?v=1" defer></script>
@include('games.partials.guide')
</x-layout>
