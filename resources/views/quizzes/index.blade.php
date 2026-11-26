<x-layout :breadcrumbs="['Quizzes' => null]" :schema="request()->hasAny(['level', 'page']) ? [] : \App\Support\Schema::itemList('Free online quizzes', collect($quizzes->items())->map(fn ($q) => [route('quizzes.show', $q), $q->title]))" title="Free Online Quizzes — Trivia With Explained Answers | BuntyFun" description="Free quizzes on general knowledge, geography, history, science, animals, maths, computers, sports, Pakistan and more. Every trivia quiz explains the right answers at the end.">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">Quizzes</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">Short quizzes you can finish in a few minutes on your phone. Trivia quizzes show your score and then explain every answer, so you learn something whether you get it right or not. Facts are checked against the sources listed on each quiz. No sign-up needed; share your result link with friends to compare.</p>
        <p class="mt-3 text-sm"><a href="{{ route('daily') }}" class="font-semibold text-navy-600 underline">Today's Daily Challenge →</a> a new quiz, five trivia questions and a quote every day.</p>

        @include('quizzes.partials.topic-chips', ['current' => null])
        <nav aria-label="Difficulty" class="mt-3 flex flex-wrap gap-2 text-sm">
            <span class="py-1 text-slate-500">Level:</span>
            <a href="{{ route('quizzes.index') }}" rel="nofollow" class="rounded-full px-3 py-1 {{ $level ? 'bg-slate-100' : 'bg-navy-600 text-white' }}">Any</a>
            @foreach (\App\Models\Quiz::DIFFICULTIES as $key => $label)
                <a href="{{ route('quizzes.index', ['level' => $key]) }}" rel="nofollow" class="rounded-full px-3 py-1 {{ $level === $key ? 'bg-navy-600 text-white' : 'bg-slate-100' }}">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($quizzes as $quiz)
                @include('quizzes.partials.card')
            @empty
                <p class="text-sm text-slate-500">No quizzes published yet.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $quizzes->links() }}</div>
        <x-explore group="quizzes" />
    </div>
</x-layout>
