@if (! empty($topics))
    <nav aria-label="Quiz topics" class="mt-6 flex flex-wrap gap-2">
        <a href="{{ route('quizzes.index') }}" class="rounded-full border px-3 py-1.5 text-sm font-medium {{ empty($current) ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 bg-white hover:border-brand-300' }}">All</a>
        @foreach ($topics as $key => $count)
            <a href="{{ route('quizzes.topic', $key) }}" class="rounded-full border px-3 py-1.5 text-sm font-medium {{ ($current ?? null) === $key ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 bg-white hover:border-brand-300' }}">
                {{ \App\Content\QuizTopics::ALL[$key]['emoji'] }} {{ \App\Content\QuizTopics::label($key) }} <span class="opacity-70">{{ $count }}</span>
            </a>
        @endforeach
    </nav>
@endif
