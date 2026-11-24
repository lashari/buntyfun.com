@php $trivia = app(\App\Services\Library\DailyPicker::class)->trivia(); @endphp
<x-layout :title="$game->title.' — '.now()->format('M j, Y').' | BuntyFun'" :breadcrumbs="['Games' => route('games.index'), $game->title => null]" :description="\App\Content\GameGuides::for($game->slug)['summary'].' New questions every day, free.'">
    <div class="mx-auto max-w-xl px-4 py-8">
        <h1 class="text-center text-2xl font-bold sm:text-3xl">{{ $game->title }}</h1>
        <p class="mt-1 text-center text-sm text-slate-500">{{ now()->format('l, F j, Y') }}</p>
        <div class="mt-6">@include('daily.partials.trivia')</div>
        <p class="mt-4 text-center text-sm"><a href="{{ route('daily') }}" class="font-semibold text-navy-600 underline">See today's quiz, game and quote →</a></p>
    </div>
    @include('games.partials.guide')
</x-layout>
