<x-layout title="Quizzes — BuntyFun" description="Fun personality, trivia and friendship quizzes.">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">Quizzes</h1>
        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            @forelse ($quizzes as $quiz)
                <a href="{{ route('quizzes.show', $quiz) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                    <h2 class="font-semibold">{{ $quiz->title }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $quiz->questions_count ?? $quiz->questions()->count() }} questions</p>
                </a>
            @empty
                <p class="text-sm text-slate-500">No quizzes published yet.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $quizzes->links() }}</div>
    </div>
</x-layout>
