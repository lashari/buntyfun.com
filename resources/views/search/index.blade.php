<x-layout :title="'Search: '.$q.' — BuntyFun'" description="Search quizzes, games, birthday wishes, Islamic names and Urdu content.">
    <div class="mx-auto max-w-4xl px-4 py-10">
        <h1 class="text-2xl font-bold">Search results for &ldquo;{{ $q }}&rdquo;</h1>

        @php $hasAny = $results['quizzes']->count() + $results['games']->count() + $results['wishes']->count() + $results['names']->count() + $results['urdu']->count(); @endphp

        @if ($q === '')
            <p class="mt-4 text-slate-500">Type something in the search box above.</p>
        @elseif ($hasAny === 0)
            <p class="mt-4 text-slate-500">No results found.</p>
        @else
            @foreach (['quizzes' => 'Quizzes', 'games' => 'Games', 'wishes' => 'Birthday Wishes', 'names' => 'Islamic Names', 'urdu' => 'اردو'] as $key => $label)
                @if ($results[$key]->isNotEmpty())
                    <h2 class="mt-6 text-lg font-semibold">{{ $label }}</h2>
                    <ul class="mt-2 space-y-2">
                        @foreach ($results[$key] as $item)
                            <li>
                                @if ($key === 'quizzes')
                                    <a href="{{ route('quizzes.show', $item) }}" class="text-violet-600 hover:underline">{{ $item->title }}</a>
                                @elseif ($key === 'games')
                                    <a href="{{ route('games.show', $item) }}" class="text-violet-600 hover:underline">{{ $item->title }}</a>
                                @elseif ($key === 'wishes')
                                    <a href="{{ route('birthday-messages.show', $item) }}" class="text-violet-600 hover:underline">{{ Str::limit($item->body, 80) }}</a>
                                @elseif ($key === 'urdu')
                                    <a href="{{ route('urdu-content.show', $item) }}" class="text-violet-600 hover:underline" dir="rtl">{{ Str::limit($item->body, 80) }}</a>
                                @else
                                    <a href="{{ route('name-entries.show', $item) }}" class="text-violet-600 hover:underline">{{ $item->name_en }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endforeach
        @endif
    </div>
</x-layout>
