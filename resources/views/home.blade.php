<x-layout title="BuntyFun — Free Games, Quizzes, Birthday Wishes & AI Fun" description="Free browser games, fun quizzes, birthday wishes, Islamic baby names, Urdu messages and AI fun tools. No sign-up, play in your browser.">
    @php
        $tiles = [
            [route('games.index'), '🎮', 'Games', 'Play in your browser', 'bg-navy-50 text-navy-700'],
            [route('daily'), '📅', 'Daily Quiz', '5 questions, every day', 'bg-sea-50 text-sea-700'],
            [route('quizzes.index'), '🧠', 'Quizzes', 'Answers explained', 'bg-sea-50 text-sea-700'],
            [route('birthday-messages.index'), '🎂', 'Birthday Wishes', 'Copy or WhatsApp', 'bg-brand-50 text-brand-700'],
            [route('messages.index'), '💬', 'Messages', 'For every occasion', 'bg-brand-50 text-brand-700'],
            [route('name-entries.index'), '🌙', 'Islamic Names', 'Meanings with sources', 'bg-sun-50 text-brand-800'],
            [route('islamic.allah-names'), '✨', '99 Names of Allah', 'Asma ul Husna', 'bg-sun-50 text-brand-800'],
            [route('duas.index'), '🤲', 'Daily Duas', 'Arabic, Urdu & English', 'bg-sun-50 text-brand-800'],
            [route('poetry.index'), '📜', 'Urdu Poetry', 'غالب، اقبال، میر', 'bg-brand-50 text-brand-700'],
            [route('urdu-content.page', 'urdu-quotes'), '✍️', 'Urdu Quotes', 'اردو اقوال', 'bg-sea-50 text-sea-700'],
            [route('wallpapers.index'), '🖼️', 'Wallpapers', 'Free HD downloads', 'bg-navy-50 text-navy-700'],
            [route('ai-tools.index'), '🤖', 'AI Fun Tools', 'Jokes, greetings & more', 'bg-navy-50 text-navy-700'],
        ];
    @endphp
    <section class="px-3 pt-4 sm:px-4 sm:pt-6">
        <div class="relative mx-auto max-w-6xl overflow-hidden rounded-3xl bg-cream bg-cover bg-center px-6 py-16 text-center shadow-sm ring-1 ring-brand-100 sm:py-24"
             style="background-image: url('{{ asset('images/banners/hero-bg.webp') }}')">
            <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1 text-xs font-semibold uppercase tracking-widest text-sea-700 ring-1 ring-sea-500/30">Free · No sign-up · Play in your browser</p>
            <h1 class="mt-5 text-4xl font-extrabold tracking-tight sm:text-6xl">Your Daily Dose of <span class="relative whitespace-nowrap text-brand-500">Fun.<svg aria-hidden="true" viewBox="0 0 120 12" class="absolute -bottom-2 left-0 h-3 w-full" preserveAspectRatio="none"><path d="M2 8 Q60 0 118 6" stroke="#F57C00" stroke-width="4" fill="none" stroke-linecap="round"/></svg></span></h1>
            <p class="mt-6 text-base font-semibold text-navy-600 sm:text-lg">Play <span class="text-brand-500">•</span> Discover <span class="text-brand-500">•</span> Celebrate <span class="text-brand-500">•</span> Enjoy</p>
            <p class="mx-auto mt-3 max-w-xl text-slate-600">Games, quizzes, birthday wishes, Islamic names and fun AI tools for kids and families — something new every day.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('games.index') }}" class="rounded-full bg-brand-600 px-7 py-3 font-semibold text-white shadow-lg shadow-brand-500/30 transition hover:-translate-y-0.5 hover:bg-brand-700">Start Exploring →</a>
                <a href="{{ route('quizzes.index') }}" class="rounded-full border-2 border-navy-700 bg-white/90 px-7 py-3 font-semibold text-navy-700 transition hover:bg-navy-700 hover:text-white">Take a Quiz</a>
            </div>
        </div>
    </section>

    <nav aria-label="Explore BuntyFun" class="mx-auto mt-8 grid max-w-6xl grid-cols-2 gap-3 px-4 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ($tiles as [$href, $icon, $label, $sub, $tone])
            <a href="{{ $href }}" class="group rounded-2xl border border-slate-200 bg-white p-4 text-center transition hover:-translate-y-1 hover:border-brand-300 hover:shadow-lg">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl text-2xl {{ $tone }}" aria-hidden="true">{{ $icon }}</span>
                <span class="mt-3 block font-semibold text-navy-700 group-hover:text-brand-600">{{ $label }}</span>
                <span class="mt-0.5 block text-xs text-slate-500 {{ preg_match('/\p{Arabic}/u', $sub) ? 'font-urdu' : '' }}" @if (preg_match('/\p{Arabic}/u', $sub)) lang="ur" dir="rtl" @endif>{{ $sub }}</span>
            </a>
        @endforeach
    </nav>

    @if (now()->month >= 11 || (now()->month === 1 && now()->day <= 15))
        <div class="mx-auto mt-6 max-w-6xl px-4">
            <a href="{{ route('messages.new-year') }}" class="flex flex-wrap items-center justify-between gap-2 rounded-2xl bg-navy-700 px-5 py-4 text-white transition hover:bg-navy-600">
                <span class="font-semibold">🎆 New Year Wishes {{ \App\Content\NewYearWishes::YEAR }} <span class="font-urdu font-normal text-sun-400" lang="ur">· نیا سال مبارک</span></span>
                <span class="text-sm text-white/80">For family, friends, love &amp; work — copy or WhatsApp →</span>
            </a>
        </div>
    @endif

    <div class="mx-auto max-w-6xl space-y-12 px-4 py-12">
        @php $dailyQuote = \Illuminate\Support\Facades\Schema::hasTable('quotes') ? app(\App\Services\Library\DailyPicker::class)->item('quote') : null; @endphp
        <section x-data="{ streak: 0, done: false }" x-init="streak = BF.streak('daily_trivia'); done = BF.doneToday('daily_trivia')">
            <a href="{{ route('daily') }}" class="block rounded-2xl bg-linear-to-r from-navy-600 to-brand-600 p-6 text-white shadow-md transition hover:shadow-lg sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-wide text-white/80">{{ now()->format('l, F j') }}</p>
                <h2 class="mt-1 text-2xl font-bold">Today's Daily Challenge</h2>
                <p class="mt-1 text-white/90">5 trivia questions, a quiz, a game and a quote. About two minutes.</p>
                @if ($dailyQuote)<p class="mt-3 text-sm italic text-white/90">“{{ Str::limit($dailyQuote->body, 120) }}” — {{ $dailyQuote->author }}</p>@endif
                <span class="mt-4 inline-block rounded-full bg-white px-5 py-2 font-semibold text-navy-700" x-text="done ? '✓ Done today · 🔥 ' + streak + ' day streak' : (streak ? 'Keep your 🔥 ' + streak + '-day streak' : 'Play now')">Play now</span>
            </a>
        </section>
        <x-my-games heading="Your games and quizzes" />
        <section aria-labelledby="h-games">
            <x-section-head id="h-games" :title="$gamesArePopular ? 'Popular Games' : 'Games'" :href="route('games.index')" more="All games"
                            :sub="$gamesArePopular ? 'Most played on BuntyFun in the last 30 days' : null" />
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                @forelse ($games as $game)
                    <a href="{{ route('games.show', $game) }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        @if ($thumb = \App\Content\OssGames::thumb($game->slug))
                            <img src="{{ $thumb }}" alt="{{ $game->title }} game preview" width="400" height="250" loading="lazy" class="aspect-[8/5] w-full object-cover">
                        @endif
                        <h3 class="p-4 font-semibold">{{ $game->title }}</h3>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No games published yet.</p>
                @endforelse
            </div>
        </section>

        <section aria-labelledby="h-quizzes">
            <x-section-head id="h-quizzes" title="Quizzes" :href="route('quizzes.index')" more="All quizzes" />
            <div class="grid gap-4 sm:grid-cols-3">
                @if ($dailyQuiz)
                    <a href="{{ route('quizzes.show', $dailyQuiz) }}" class="rounded-2xl bg-sea-50 p-5 shadow-sm ring-1 ring-sea-500/30 transition hover:-translate-y-0.5 hover:shadow-md">
                        <p class="text-xs font-semibold uppercase tracking-wide text-sea-700">Quiz of the day</p>
                        <h3 class="mt-1 font-semibold">{{ $dailyQuiz->title }}</h3>
                    </a>
                @endif
                @foreach ($quizzes->reject(fn ($q) => $dailyQuiz && $q->is($dailyQuiz))->take($dailyQuiz ? 2 : 3) as $quiz)
                    <a href="{{ route('quizzes.show', $quiz) }}" class="rounded-2xl border border-slate-200 border-t-4 border-t-brand-400 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <h3 class="font-semibold">{{ $quiz->title }}</h3>
                    </a>
                @endforeach
            </div>
        </section>

        <section aria-labelledby="h-wishes">
            <x-section-head id="h-wishes" title="Birthday Wishes" :href="route('birthday-messages.index')" more="All birthday wishes" />
            <x-link-chips class="mb-4" label="Birthday wishes by relationship" :links="collect($relationships)->map(fn ($r) => [route('birthday-messages.relationship', \App\Models\BirthdayMessage::relationshipSlug($r)), 'For '.Str::lower(Str::headline($r))])->push([route('birthday-messages.urdu'), 'Urdu birthday wishes'])->all()" />
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ($wishes as $wish)
                    <a href="{{ route('birthday-messages.show', $wish) }}" class="rounded-2xl border border-slate-200 border-t-4 border-t-brand-400 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <p class="text-sm text-slate-600" @if ($wish->language === 'ur') dir="rtl" lang="ur" @endif>{{ Str::limit($wish->body, 90) }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        <section aria-labelledby="h-messages">
            <x-section-head id="h-messages" title="Messages for Every Occasion" :href="route('messages.index')" more="All messages" />
            <x-link-chips :links="collect(\App\Content\MessagePages::PAGES)->map(fn ($p, $url) => [route('messages.page', $url), $p['h1']])->values()->prepend([route('messages.new-year'), 'New Year wishes'])->all()" />
        </section>

        <section aria-labelledby="h-islamic">
            <x-section-head id="h-islamic" title="Islamic" :href="route('name-entries.index')" more="Islamic names" />
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold"><a href="{{ route('name-entries.index') }}" class="hover:text-brand-600">Islamic Names</a></h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($names as $name)
                            <li><a href="{{ route('name-entries.show', $name) }}" class="text-navy-600 hover:underline">{{ $name->name_en }}</a> <span class="text-slate-500">— {{ Str::limit($name->meaning, 50) }}</span></li>
                        @endforeach
                    </ul>
                    <p class="mt-4 flex gap-3 text-sm font-semibold"><a href="{{ route('name-entries.list', 'boys') }}" class="text-brand-600 hover:underline">Boys names</a><a href="{{ route('name-entries.list', 'girls') }}" class="text-brand-600 hover:underline">Girls names</a></p>
                </div>
                <a href="{{ route('islamic.allah-names') }}" class="block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <h3 class="font-semibold">99 Names of Allah</h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($allahNames as $n)
                            <li class="flex items-baseline justify-between gap-2"><span>{{ $n->transliteration }} <span class="text-slate-500">— {{ Str::limit($n->meaning_en, 30) }}</span></span><span class="text-lg text-navy-700" dir="rtl" lang="ar">{{ $n->arabic }}</span></li>
                        @endforeach
                    </ul>
                    <span class="mt-4 block text-sm font-semibold text-brand-600">All 99 names with meanings →</span>
                </a>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold"><a href="{{ route('duas.index') }}" class="hover:text-brand-600">Daily Duas</a></h3>
                    @if ($dua)
                        <a href="{{ route('duas.show', $dua) }}" class="mt-3 block hover:opacity-80">
                            <span class="block text-sm font-medium">{{ $dua->title }}</span>
                            <span class="mt-1 block text-right text-xl leading-loose text-navy-700" dir="rtl" lang="ar">{{ $dua->arabic }}</span>
                            <span class="block text-sm text-slate-600">{{ Str::limit($dua->translation_en, 90) }}</span>
                        </a>
                    @endif
                    <p class="mt-4 flex gap-3 text-sm font-semibold"><a href="{{ route('duas.index') }}" class="text-brand-600 hover:underline">All duas</a><a href="{{ route('islamic.calendar') }}" class="text-brand-600 hover:underline">Islamic calendar</a></p>
                </div>
            </div>
        </section>

        <section aria-labelledby="h-urdu">
            <x-section-head id="h-urdu" title="Urdu Poetry & Quotes" :href="route('poetry.index')" more="Urdu poetry" />
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    @if ($poem)
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Poetry of the day</p>
                        @include('poetry.partials.card', ['poem' => $poem])
                    @endif
                    <x-link-chips class="mt-4" label="Urdu poets" :links="$poets->map(fn ($p) => [route('poetry.segment', $p->slug), $p->name_en.' poetry'])->all()" />
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold"><a href="{{ route('urdu-content.page', 'urdu-quotes') }}" class="hover:text-brand-600">Urdu Quotes</a> <span class="font-urdu font-normal text-slate-500" lang="ur" dir="rtl">اردو اقوال</span></h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach (\App\Content\UrduPages::PAGES as $url => $page)
                            <li><a href="{{ route('urdu-content.page', $url) }}" class="text-navy-600 hover:underline">{{ $page['subtitle'] }}</a></li>
                        @endforeach
                        <li><a href="{{ route('urdu-content.index') }}" class="text-navy-600 hover:underline">Urdu corner: all Urdu messages</a></li>
                    </ul>
                </div>
            </div>
        </section>

        @if ($wallpapers->isNotEmpty())
        <section aria-labelledby="h-wallpapers">
            <x-section-head id="h-wallpapers" title="Wallpapers" :href="route('wallpapers.index')" more="All wallpapers" />
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ($wallpapers as $wallpaper)
                    <a href="{{ route('wallpapers.show', $wallpaper) }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                        <img src="{{ $wallpaper->thumbUrl() }}" alt="{{ $wallpaper->title }}" width="400" height="225" loading="lazy" class="aspect-video w-full object-cover">
                        <p class="p-3 font-semibold">{{ $wallpaper->title }}</p>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

        <section aria-labelledby="h-ai">
            <x-section-head id="h-ai" title="AI Fun Tools" :href="route('ai-tools.index')" more="All AI tools" />
            <div class="grid gap-4 sm:grid-cols-3">
                @forelse ($aiTools as $tool)
                    <a href="{{ route('ai-tools.show', $tool) }}" class="rounded-2xl border border-slate-200 border-t-4 border-t-brand-400 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <h3 class="font-semibold">{{ $tool->title }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $tool->description }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No AI tools published yet.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 text-sm leading-relaxed text-slate-600">
            <h2 class="text-lg font-bold">About BuntyFun</h2>
            <p class="mt-2">BuntyFun is a free site for small everyday moments: copy a birthday wish, look up what an Islamic name means, play a two-minute game or test yourself with a quiz. Everything works in your browser without an account. Wishes are grouped by who they are for, name meanings link to their sources, and quizzes explain every answer. <a href="{{ route('pages.about') }}" class="text-navy-600 underline">Read more about how we make our content</a>.</p>
        </section>
    </div>
</x-layout>
