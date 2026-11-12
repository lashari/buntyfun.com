<x-layout title="BuntyFun — Free Games, Quizzes, Birthday Wishes & AI Fun" description="Free browser games, fun quizzes, birthday wishes, Islamic baby names, Urdu poetry and AI fun tools. No sign-up, play in your browser.">
    <section class="bg-gradient-to-b from-violet-50 to-white px-4 py-16 text-center">
        <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">Your Daily Dose of Fun.</h1>
        <p class="mx-auto mt-4 max-w-xl text-lg text-slate-600">
            Play exciting games, discover fun quizzes, create personalized wishes and explore
            something new every day.
        </p>
        <div class="mt-8 flex justify-center gap-4">
            <a href="{{ route('quizzes.index') }}" class="rounded-full bg-violet-600 px-6 py-3 font-semibold text-white hover:bg-violet-700">Explore Fun</a>
            <a href="{{ route('games.index') }}" class="rounded-full border border-violet-200 bg-white px-6 py-3 font-semibold text-violet-700 hover:bg-violet-50">Play a Game</a>
        </div>
    </section>

    <div class="mx-auto max-w-6xl space-y-12 px-4 py-12">
        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold">Trending Quizzes</h2>
                <a href="{{ route('quizzes.index') }}" class="text-sm font-medium text-violet-600 hover:underline">See all</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                @forelse ($quizzes as $quiz)
                    <a href="{{ route('quizzes.show', $quiz) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                        <h3 class="font-semibold">{{ $quiz->title }}</h3>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No quizzes published yet.</p>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold">Popular Games</h2>
                <a href="{{ route('games.index') }}" class="text-sm font-medium text-violet-600 hover:underline">See all</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                @forelse ($games as $game)
                    <a href="{{ route('games.show', $game) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                        <h3 class="font-semibold">{{ $game->title }}</h3>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No games published yet.</p>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold">Birthday Wishes</h2>
                <a href="{{ route('birthday-messages.index') }}" class="text-sm font-medium text-violet-600 hover:underline">See all</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                @forelse ($wishes as $wish)
                    <a href="{{ route('birthday-messages.show', $wish) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                        <p class="text-sm text-slate-600">{{ Str::limit($wish->body, 90) }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No wishes published yet.</p>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold">Islamic Names</h2>
                <a href="{{ route('name-entries.index') }}" class="text-sm font-medium text-violet-600 hover:underline">See all</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                @forelse ($names as $name)
                    <a href="{{ route('name-entries.show', $name) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                        <h3 class="font-semibold">{{ $name->name_en }}</h3>
                        <p class="text-sm text-slate-500">{{ Str::limit($name->meaning, 60) }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No names published yet.</p>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold">Wallpapers</h2>
                <a href="{{ route('wallpapers.index') }}" class="text-sm font-medium text-violet-600 hover:underline">See all</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                @forelse ($wallpapers as $wallpaper)
                    <a href="{{ route('wallpapers.show', $wallpaper) }}" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm hover:shadow-md">
                        <img src="{{ $wallpaper->url() }}" alt="{{ $wallpaper->title }}" loading="lazy" class="aspect-video w-full object-cover">
                        <p class="p-3 font-semibold">{{ $wallpaper->title }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No wallpapers published yet.</p>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold">AI Fun Studio</h2>
                <a href="{{ route('ai-tools.index') }}" class="text-sm font-medium text-violet-600 hover:underline">See all</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                @forelse ($aiTools as $tool)
                    <a href="{{ route('ai-tools.show', $tool) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                        <h3 class="font-semibold">{{ $tool->title }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $tool->description }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No AI tools published yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layout>
