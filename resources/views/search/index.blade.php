@php
    $sections = ['quizzes' => 'Quizzes', 'games' => 'Games', 'quotes' => 'Quotes', 'wishes' => 'Birthday Wishes', 'messages' => 'Messages',
        'names' => 'Islamic Names', 'urdu' => 'اردو', 'wallpapers' => 'Wallpapers', 'aiTools' => 'AI Fun Studio', 'poems' => 'Urdu Poetry', 'duas' => 'Duas'];
    $hasAny = array_sum(array_map(fn ($c) => $c->count(), $results));
@endphp
<x-layout :title="($q !== '' ? 'Search: '.$q : 'Search').' — BuntyFun'" description="Search quizzes, games, quotes, birthday wishes, messages, Islamic names, Urdu content and wallpapers." :noindex="true" :ads="false">
    <div class="mx-auto max-w-4xl px-4 py-10">
        <h1 class="text-2xl font-bold">{{ $q !== '' ? 'Search results for “'.$q.'”' : 'Search BuntyFun' }}</h1>

        <form action="{{ route('search') }}" method="GET" role="search" class="mt-4 flex max-w-2xl gap-2">
            <label for="search-page-q" class="sr-only">Search</label>
            <input id="search-page-q" type="search" name="q" value="{{ $q }}" placeholder="Try: einstein, cricket, birthday, mango, ali…" @if ($q === '') autofocus @endif
                   class="min-w-0 flex-1 rounded-full border border-slate-300 px-4 py-2 text-base focus:border-brand-500 focus:outline-none">
            <button type="submit" class="rounded-full bg-brand-600 px-5 py-2 font-semibold text-white hover:bg-brand-700">Search</button>
        </form>

        @if ($q === '')
            <p class="mt-4 text-slate-500">Search quizzes, games, quotes, birthday wishes, messages, Islamic names, Urdu lines and wallpapers.</p>
        @elseif (mb_strlen($q) < 2)
            <p class="mt-4 text-slate-500">Type at least 2 letters.</p>
        @elseif ($hasAny === 0)
            <p class="mt-4 text-slate-500">No results for “{{ $q }}”. Try a shorter word, or browse <a href="{{ route('quizzes.index') }}" class="underline">quizzes</a>, <a href="{{ route('games.index') }}" class="underline">games</a> or <a href="{{ route('quotes.index') }}" class="underline">quotes</a>.</p>
        @else
            <nav class="mt-4 flex flex-wrap gap-2 text-sm" aria-label="Jump to section">
                @foreach ($sections as $key => $label)
                    @if ($results[$key]->isNotEmpty())
                        <a href="#s-{{ $key }}" class="rounded-full bg-slate-100 px-3 py-1 hover:bg-brand-50">{{ $label }} <span class="text-slate-500">{{ $results[$key]->count() }}{{ $results[$key]->count() >= \App\Http\Controllers\SearchController::PER_SECTION ? '+' : '' }}</span></a>
                    @endif
                @endforeach
            </nav>
            @foreach ($sections as $key => $label)
                @if ($results[$key]->isNotEmpty())
                    <h2 id="s-{{ $key }}" class="mt-8 text-lg font-semibold">{{ $label }}</h2>
                    <ul class="mt-2 space-y-2">
                        @foreach ($results[$key] as $item)
                            <li>
                                @switch($key)
                                    @case('quizzes') <a href="{{ route('quizzes.show', $item) }}" class="text-navy-600 hover:underline">{{ $item->title }}</a> @break
                                    @case('games') <a href="{{ route('games.show', $item) }}" class="text-navy-600 hover:underline">{{ $item->title }}</a> @break
                                    @case('quotes') <a href="{{ route('quotes.show', $item) }}" class="text-navy-600 hover:underline">“{{ Str::limit($item->body, 90) }}”</a> <span class="text-sm text-slate-500">— {{ $item->author }}</span> @break
                                    @case('wishes') <a href="{{ route('birthday-messages.show', $item) }}" class="text-navy-600 hover:underline">{{ Str::limit($item->body, 90) }}</a> @break
                                    @case('messages') <a href="{{ route('messages.show', $item) }}" class="text-navy-600 hover:underline">{{ Str::limit($item->title ?: $item->body, 90) }}</a> <span class="text-sm text-slate-500">· {{ $item->categoryLabel() }}</span> @break
                                    @case('names') <a href="{{ route('name-entries.show', $item) }}" class="text-navy-600 hover:underline">{{ $item->name_en }}</a> <span class="text-sm text-slate-500">— {{ Str::limit($item->meaning, 60) }}</span> @break
                                    @case('urdu') <a href="{{ route('urdu-content.show', $item) }}" class="text-navy-600 hover:underline" dir="rtl">{{ Str::limit($item->body, 90) }}</a> @break
                                    @case('wallpapers') <a href="{{ route('wallpapers.show', $item) }}" class="text-navy-600 hover:underline">{{ $item->title }}</a> @break
                                    @case('poems') <a href="{{ route('poetry.show', [$item->poet->slug, $item->slug]) }}" class="font-urdu text-navy-600 hover:underline" dir="rtl" lang="ur">{{ Str::limit($item->firstLines(1), 90) }}</a> <span class="text-sm text-slate-500">— {{ $item->poet->name_en }}</span> @break
                                    @case('duas') <a href="{{ route('duas.show', $item) }}" class="text-navy-600 hover:underline">{{ $item->title }}</a> @break
                                    @case('aiTools') <a href="{{ route('ai-tools.show', $item) }}" class="text-navy-600 hover:underline">{{ $item->title }}</a> @break
                                @endswitch
                            </li>
                        @endforeach
                    </ul>
                    @if ($key === 'quotes' && $results[$key]->count() >= \App\Http\Controllers\SearchController::PER_SECTION)
                        <a href="{{ route('quotes.index', ['q' => $q]) }}" class="mt-2 inline-block text-sm font-semibold text-navy-600 underline">All quotes matching “{{ $q }}” →</a>
                    @endif
                @endif
            @endforeach
        @endif
    </div>
</x-layout>
