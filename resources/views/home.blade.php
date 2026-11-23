<x-layout title="BuntyFun — Free Games, Quizzes, Birthday Wishes & AI Fun" description="Free browser games, fun quizzes, birthday wishes, Islamic baby names, Urdu messages and AI fun tools. No sign-up, play in your browser.">
    @php
        $tiles = [
            [route('games.index'), '🎮', 'Free Games', 'Play in your browser', 'bg-navy-50 text-navy-700'],
            [route('quizzes.index'), '🧠', 'Fun Quizzes', 'Trivia with answers explained', 'bg-sea-50 text-sea-700'],
            [route('birthday-messages.index'), '🎂', 'Birthday Wishes', 'Copy or send on WhatsApp', 'bg-brand-50 text-brand-700'],
            [route('name-entries.index'), '🌙', 'Islamic Names', 'Meanings with sources', 'bg-sun-50 text-brand-800'],
            [route('urdu-content.index'), '✍️', 'Urdu Corner', 'اردو پیغامات', 'bg-sea-50 text-sea-700'],
            [route('ai-tools.index'), '✨', 'AI Fun Tools', 'Jokes, greetings & more', 'bg-navy-50 text-navy-700'],
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

    <nav aria-label="Sections" class="mx-auto mt-8 grid max-w-6xl grid-cols-2 gap-3 px-4 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ($tiles as [$href, $icon, $label, $sub, $tone])
            <a href="{{ $href }}" class="group rounded-2xl border border-slate-200 bg-white p-4 text-center transition hover:-translate-y-1 hover:border-brand-300 hover:shadow-lg">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl text-2xl {{ $tone }}" aria-hidden="true">{{ $icon }}</span>
                <span class="mt-3 block font-semibold text-navy-700 group-hover:text-brand-600">{{ $label }}</span>
                <span class="mt-0.5 block text-xs text-slate-500 {{ str_contains($sub, 'اردو') ? 'font-urdu' : '' }}">{{ $sub }}</span>
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
        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold"><span class="mr-2 inline-block h-6 w-1.5 rounded-full bg-brand-500 align-[-3px]"></span>Quizzes</h2>
                <a href="{{ route('quizzes.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">See all →</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                @forelse ($quizzes as $quiz)
                    <a href="{{ route('quizzes.show', $quiz) }}" class="rounded-2xl border border-slate-200 border-t-4 border-t-brand-400 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <h3 class="font-semibold">{{ $quiz->title }}</h3>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No quizzes published yet.</p>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold"><span class="mr-2 inline-block h-6 w-1.5 rounded-full bg-brand-500 align-[-3px]"></span>Games</h2>
                <a href="{{ route('games.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">See all →</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                @forelse ($games as $game)
                    <a href="{{ route('games.show', $game) }}" class="rounded-2xl border border-slate-200 border-t-4 border-t-brand-400 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <h3 class="font-semibold">{{ $game->title }}</h3>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No games published yet.</p>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold"><span class="mr-2 inline-block h-6 w-1.5 rounded-full bg-brand-500 align-[-3px]"></span>Birthday Wishes</h2>
                <a href="{{ route('birthday-messages.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">See all →</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                @forelse ($wishes as $wish)
                    <a href="{{ route('birthday-messages.show', $wish) }}" class="rounded-2xl border border-slate-200 border-t-4 border-t-brand-400 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <p class="text-sm text-slate-600" @if ($wish->language === 'ur') dir="rtl" lang="ur" @endif>{{ Str::limit($wish->body, 90) }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No wishes published yet.</p>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold"><span class="mr-2 inline-block h-6 w-1.5 rounded-full bg-brand-500 align-[-3px]"></span>Islamic Names</h2>
                <a href="{{ route('name-entries.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">See all →</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                @forelse ($names as $name)
                    <a href="{{ route('name-entries.show', $name) }}" class="rounded-2xl border border-slate-200 border-t-4 border-t-brand-400 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <h3 class="font-semibold">{{ $name->name_en }}</h3>
                        <p class="text-sm text-slate-500">{{ Str::limit($name->meaning, 60) }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No names published yet.</p>
                @endforelse
            </div>
        </section>

        @if ($wallpapers->isNotEmpty())
        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold"><span class="mr-2 inline-block h-6 w-1.5 rounded-full bg-brand-500 align-[-3px]"></span>Wallpapers</h2>
                <a href="{{ route('wallpapers.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">See all →</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                @forelse ($wallpapers as $wallpaper)
                    <a href="{{ route('wallpapers.show', $wallpaper) }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                        <img src="{{ $wallpaper->thumbUrl() }}" alt="{{ $wallpaper->title }}" loading="lazy" class="aspect-video w-full object-cover">
                        <p class="p-3 font-semibold">{{ $wallpaper->title }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No wallpapers published yet.</p>
                @endforelse
            </div>
        </section>
        @endif

        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold"><span class="mr-2 inline-block h-6 w-1.5 rounded-full bg-brand-500 align-[-3px]"></span>AI Fun Studio</h2>
                <a href="{{ route('ai-tools.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">See all →</a>
            </div>
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
