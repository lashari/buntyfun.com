<x-layout title="Free Online Quizzes — Trivia With Explained Answers | BuntyFun" description="Free quizzes on Pakistan, Islamic history, cricket, flags, science, English words and more. Every trivia quiz explains the right answers at the end.">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">Quizzes</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">Short quizzes you can finish in a few minutes on your phone. Trivia quizzes show your score and then explain every answer, so you learn something whether you get it right or not. Facts are checked against the sources listed on each quiz. No sign-up needed; share your result link with friends to compare.</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($quizzes as $quiz)
                <a href="{{ route('quizzes.show', $quiz) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                    <h2 class="font-semibold">{{ $quiz->title }}</h2>
                    @if ($quiz->intro)<p class="mt-1 text-sm text-slate-600">{{ Str::limit($quiz->intro, 110) }}</p>@endif
                    <p class="mt-3 flex flex-wrap gap-2 text-xs text-slate-500">
                        <span class="rounded-full bg-violet-50 px-2 py-0.5 text-violet-700">{{ $quiz->questions_count ?? $quiz->questions()->count() }} questions</span>
                        @if ($quiz->difficulty)<span class="rounded-full bg-slate-100 px-2 py-0.5">{{ \App\Models\Quiz::DIFFICULTIES[$quiz->difficulty] ?? ucfirst($quiz->difficulty) }}</span>@endif
                    </p>
                </a>
            @empty
                <p class="text-sm text-slate-500">No quizzes published yet.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $quizzes->links() }}</div>
    </div>
</x-layout>
