<a href="{{ route('quizzes.show', $quiz) }}" class="group flex flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
    @if ($quiz->topic && isset(\App\Content\QuizTopics::ALL[$quiz->topic]))
        <span class="text-xs font-semibold uppercase tracking-wide text-navy-600">{{ \App\Content\QuizTopics::ALL[$quiz->topic]['emoji'] }} {{ \App\Content\QuizTopics::label($quiz->topic) }}</span>
    @endif
    <h2 class="mt-1 font-semibold group-hover:text-navy-600">{{ $quiz->title }}</h2>
    @if ($quiz->intro)<p class="mt-1 text-sm text-slate-600">{{ Str::limit($quiz->intro, 110) }}</p>@endif
    <p class="mt-auto flex flex-wrap gap-2 pt-3 text-xs text-slate-500">
        <span class="rounded-full bg-brand-50 px-2 py-0.5 text-navy-600">{{ $quiz->questions_count ?? $quiz->questions()->count() }} questions</span>
        @if ($quiz->difficulty)<span class="rounded-full bg-slate-100 px-2 py-0.5">{{ \App\Models\Quiz::DIFFICULTIES[$quiz->difficulty] ?? ucfirst($quiz->difficulty) }}</span>@endif
    </p>
</a>
