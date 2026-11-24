<x-layout :title="$game->title.' — Play Free Online, Rules & Tips | BuntyFun'" :breadcrumbs="['Games' => route('games.index'), $game->title => null]" :description="\App\Content\GameGuides::for($game->slug)['summary'].' Free, no sign-up, works on phones.'">
    @include('games.partials.country-quiz', ['mode' => 'flag'])
    @include('games.partials.guide')
</x-layout>
