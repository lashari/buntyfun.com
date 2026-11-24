<x-admin-layout title="Quizzes">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Quizzes</h1>
        <a href="{{ route('admin.quizzes.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">New Quiz</a>
    </div>
    @if (session('status'))<p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-800">{{ session('status') }}</p>@endif
    <nav class="mt-4 flex gap-2 text-sm">
        @foreach (['' => 'All', 'in_review' => 'In review', 'published' => 'Published', 'draft' => 'Draft'] as $k => $l)
            <a href="{{ route('admin.quizzes.index', $k ? ['status' => $k] : []) }}" class="rounded-full px-3 py-1 {{ ($status ?? '') === $k ? 'bg-navy-600 text-white' : 'bg-slate-100' }}">{{ $l }}</a>
        @endforeach
        <a href="{{ route('admin.question-bank.index') }}" class="ml-auto text-navy-600 underline">Question Bank →</a>
    </nav>

    <table class="mt-6 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
                <th class="px-4 py-2">Title</th>
                <th class="px-4 py-2">Topic</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($quizzes as $quiz)
                <tr>
                    <td class="px-4 py-2">{{ $quiz->title }}</td>
                    <td class="px-4 py-2 text-slate-500">{{ $quiz->topic ? \App\Content\QuizTopics::label($quiz->topic) : '—' }} · {{ $quiz->questions_count }} q</td>
                    <td class="px-4 py-2">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $quiz->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ Str::headline($quiz->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <a href="{{ route('quizzes.show', $quiz) }}" target="_blank" class="mr-3 text-slate-600 hover:underline">Preview</a>
                        <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="text-navy-600 hover:underline">Edit</a>
                        @if (auth()->user()->canPublish())
                            @if ($quiz->status === 'published')
                                <form method="POST" action="{{ route('admin.quizzes.unpublish', $quiz) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="ml-3 text-slate-500 hover:underline">Unpublish</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.quizzes.publish', $quiz) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="ml-3 text-emerald-600 hover:underline">Publish</button>
                                </form>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $quizzes->links() }}</div>
</x-admin-layout>
