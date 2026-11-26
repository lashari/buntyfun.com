@php $guide = \App\Content\GameGuides::for($game->slug); @endphp
<x-fav-button class="mt-8 flex justify-center" :title="$game->title" :url="route('games.show', $game)" type="game" />
@if ($guide)
    <section class="mx-auto mt-12 max-w-2xl px-4 text-left prose-page">
        <h2>How to play {{ $game->title }}</h2>
        <ol>
            @foreach ($guide['how'] as $step)
                <li>{{ $step }}</li>
            @endforeach
        </ol>
        @if (! empty($guide['scoring']))
            <h2>Scoring</h2>
            <p>{{ $guide['scoring'] }}</p>
        @endif
        <h2>Tips</h2>
        <ul>
            @foreach ($guide['tips'] as $tip)
                <li>{{ $tip }}</li>
            @endforeach
        </ul>
        <p class="text-sm text-slate-500">Runs in your browser on phones, tablets and computers. Nothing to install and no sign-up.</p>
    </section>
@endif
{{-- Every game page links to the other games (also games without a written guide, e.g. the bundled open-source ones). --}}
@php $others = \App\Models\Game::where('status', 'published')->whereKeyNot($game->id)->orderBy('title')->get(); @endphp
@if ($others->isNotEmpty())
    <section class="mx-auto mt-12 max-w-2xl px-4 text-left prose-page">
        <h2>More games to play</h2>
        <ul>
            @foreach ($others as $o)
                <li><a href="{{ route('games.show', $o) }}">{{ $o->title }}</a>@if ($g = \App\Content\GameGuides::for($o->slug)) — {{ $g['summary'] }}@elseif ($oss = \App\Content\OssGames::for($o->slug)) — {{ $oss['summary'] }}@endif</li>
            @endforeach
        </ul>
        <p><a href="{{ route('games.index') }}">All free games</a> · <a href="{{ route('daily') }}">Daily Challenge</a> · <a href="{{ route('quizzes.index') }}">Quizzes</a></p>
    </section>
@endif
