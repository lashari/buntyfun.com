<x-layout :title="'Daily Challenge — Trivia, Quiz and Quote of the Day ('.now()->format('M j, Y').') | BuntyFun'"
          description="A new daily challenge every day: five trivia questions with explanations, a quiz of the day, a game of the day and a quote to share. Build your streak."
          :breadcrumbs="['Daily Challenge' => null]" :urduFont="(bool) $poem">
    <div class="mx-auto max-w-5xl px-4 py-10">
        <p class="text-sm font-semibold uppercase tracking-wide text-navy-600">{{ now()->format('l, F j, Y') }}</p>
        <h1 class="mt-1 text-3xl font-bold">Daily Challenge</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">Five quick trivia questions, a quiz, a game and a quote, new every day. Answer all five to keep your streak alive. It takes about two minutes.</p>

        <div class="mt-8 grid gap-6 lg:grid-cols-5">
            <section class="lg:col-span-3" aria-labelledby="dt">
                <h2 id="dt" class="mb-3 text-xl font-bold">🧠 Daily Trivia</h2>
                @include('daily.partials.trivia')
            </section>

            <div class="space-y-6 lg:col-span-2">
                @if ($quiz)
                    <section>
                        <h2 class="mb-3 text-xl font-bold">📝 Quiz of the day</h2>
                        <a href="{{ route('quizzes.show', $quiz) }}" class="block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                            <p class="font-semibold">{{ $quiz->title }}</p>
                            @if ($quiz->intro)<p class="mt-1 text-sm text-slate-600">{{ Str::limit($quiz->intro, 100) }}</p>@endif
                            <span class="mt-3 inline-block rounded-full bg-brand-600 px-4 py-1.5 text-sm font-semibold text-white">Start quiz</span>
                        </a>
                    </section>
                @endif
                @if ($game)
                    <section>
                        <h2 class="mb-3 text-xl font-bold">🎮 Game of the day</h2>
                        <a href="{{ route('games.show', $game) }}" class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md">
                            @if ($thumb = \App\Content\OssGames::thumb($game->slug))<img src="{{ $thumb }}" alt="" width="96" height="60" class="h-15 w-24 rounded-lg object-cover" loading="lazy">@endif
                            <span>
                                <span class="block font-semibold">{{ $game->title }}</span>
                                @if ($g = \App\Content\GameGuides::for($game->slug))<span class="block text-sm text-slate-600">{{ Str::limit($g['summary'], 80) }}</span>@endif
                            </span>
                        </a>
                    </section>
                @endif
                @if ($quote)
                    <section>
                        <h2 class="mb-3 text-xl font-bold">💬 Quote of the day</h2>
                        @include('quotes.partials.card', ['quote' => $quote])
                    </section>
                @endif
                @if ($poem)
                    <section>
                        <h2 class="mb-3 text-xl font-bold">📜 Poetry of the day</h2>
                        @include('poetry.partials.card', ['poem' => $poem])
                    </section>
                @endif
                @if ($dua)
                    <section>
                        <h2 class="mb-3 text-xl font-bold">🤲 Dua of the day</h2>
                        <a href="{{ route('duas.show', $dua) }}" class="block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md">
                            <span class="font-semibold">{{ $dua->title }}</span>
                            <span class="mt-2 block text-right text-xl leading-loose text-navy-700" dir="rtl" lang="ar">{{ $dua->arabic }}</span>
                            <span class="mt-1 block text-sm text-slate-600">{{ Str::limit($dua->translation_en, 110) }}</span>
                        </a>
                    </section>
                @endif
                <section x-data="{ items: [] }" x-init="items = BF.recent().slice(0, 5)" x-show="items.length" x-cloak>
                    <h2 class="mb-3 text-xl font-bold">Recently played</h2>
                    <ul class="space-y-1 text-sm">
                        <template x-for="it in items" :key="it.url"><li><a :href="it.url" class="text-navy-600 underline" x-text="it.title"></a></li></template>
                    </ul>
                </section>
            </div>
        </div>

        <section class="prose-page mt-12 max-w-3xl">
            <h2>How the Daily Challenge works</h2>
            <p>Every day at midnight a new set of five trivia questions appears, chosen from our reviewed question bank on general knowledge, geography, history, science, animals, maths, computers and sports. Everyone sees the same questions on the same day, so you can compare scores with friends and family.</p>
            <p>After each answer you see whether you were right and a short explanation. Finish all five to add a day to your streak. Miss a day and the streak starts again from one. Streaks are stored in your own browser; there is no sign-up.</p>
            <p>More to play: <a href="{{ route('quizzes.index') }}">all quizzes</a>, <a href="{{ route('games.index') }}">free games</a> and <a href="{{ route('quotes.index') }}">quotes</a>.</p>
        </section>
    </div>
</x-layout>
