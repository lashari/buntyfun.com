@php $guide = \App\Content\GameGuides::for($game->slug); @endphp
@if ($guide)
    <section class="mx-auto mt-12 max-w-2xl px-4 text-left prose-page">
        <h2>How to play {{ $game->title }}</h2>
        <ol>
            @foreach ($guide['how'] as $step)
                <li>{{ $step }}</li>
            @endforeach
        </ol>
        <h2>Scoring</h2>
        <p>{{ $guide['scoring'] }}</p>
        <h2>Tips</h2>
        <ul>
            @foreach ($guide['tips'] as $tip)
                <li>{{ $tip }}</li>
            @endforeach
        </ul>
        <p class="text-sm text-slate-500">Runs in your browser on phones, tablets and computers. Nothing to install and no sign-up.</p>
        @php $others = \App\Models\Game::where('status', 'published')->whereKeyNot($game->id)->orderBy('title')->get(); @endphp
        @if ($others->isNotEmpty())
            <h2>More games</h2>
            <ul>
                @foreach ($others as $o)
                    <li><a href="{{ route('games.show', $o) }}">{{ $o->title }}</a>@if ($g = \App\Content\GameGuides::for($o->slug)) — {{ $g['summary'] }}@endif</li>
                @endforeach
            </ul>
        @endif
    </section>
@endif
