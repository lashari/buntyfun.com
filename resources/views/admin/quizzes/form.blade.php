@php
    $existingQuestions = $quiz->exists
        ? $quiz->questions->map(fn ($q) => [
            'prompt' => $q->prompt,
            'explanation' => $q->explanation,
            'answers' => $q->answers->map(fn ($a) => array_filter([
                'label' => $a->label,
                'is_correct' => $a->is_correct,
                'score_value' => $a->score_value,
            ], fn ($v) => ! is_null($v)))->values(),
        ])->values()
        : collect();
@endphp
<x-admin-layout :title="$quiz->exists ? 'Edit Quiz' : 'New Quiz'">
    <h1 class="text-2xl font-bold">{{ $quiz->exists ? 'Edit Quiz' : 'New Quiz' }}</h1>

    <form method="POST" action="{{ $quiz->exists ? route('admin.quizzes.update', $quiz) : route('admin.quizzes.store') }}" class="mt-6 max-w-2xl space-y-4">
        @csrf
        @if ($quiz->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-slate-700">Category</label>
            <select name="category_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($quiz->category_id === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Topic <span class="font-normal text-slate-500">(puts the quiz on /quizzes/topic/…)</span></label>
            <select name="topic" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="">— none —</option>
                @foreach (\App\Content\QuizTopics::ALL as $key => $t)
                    <option value="{{ $key }}" @selected(old('topic', $quiz->topic) === $key)>{{ $t['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Title</label>
            <input type="text" name="title" value="{{ old('title', $quiz->title) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Intro (shown above the quiz: what it covers, who it is for)</label>
            <textarea name="intro" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('intro', $quiz->intro) }}</textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">Difficulty</label>
                <select name="difficulty" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    <option value="">—</option>
                    @foreach (\App\Models\Quiz::DIFFICULTIES as $key => $label)
                        <option value="{{ $key }}" @selected(old('difficulty', $quiz->difficulty) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Facts checked on (shown on the quiz)</label>
                <input type="date" name="reviewed_at" value="{{ old('reviewed_at', $quiz->reviewed_at?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Sources (one per line; links are clickable on the quiz)</label>
            <textarea name="sources" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('sources', $quiz->sources) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Result types for score-based quizzes (JSON: [{"min": 0.8, "label": "...", "text": "..."}], highest min wins; leave empty for trivia)</label>
            <textarea name="outcomes_json" rows="5" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs">{{ old('outcomes_json', $quiz->outcomes ? json_encode($quiz->outcomes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Slug (optional — derived from title if blank)</label>
            <input type="text" name="slug" value="{{ old('slug', $quiz->slug) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Language</label>
            <select name="language" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="en" @selected(($quiz->language ?? 'en') === 'en')>English</option>
                <option value="ur" @selected($quiz->language === 'ur')>Urdu</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="draft" @selected(($quiz->status ?? 'draft') === 'draft')>Draft</option>
                <option value="in_review" @selected($quiz->status === 'in_review')>In review</option>
                <option value="published" @selected($quiz->status === 'published')>Published</option>
            </select>
            @unless (auth()->user()->canPublish())
                <p class="mt-1 text-xs text-slate-500">Your role can submit for review, but only an admin can publish.</p>
            @endunless
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">
                Questions (JSON) — each item: <code>{"prompt": "...", "explanation": "why the answer is right", "answers": [{"label": "...", "is_correct": true}]}</code>
                or use <code>"score_value"</code> instead of <code>is_correct</code> for a personality quiz.
            </label>
            <textarea name="questions_json" rows="12" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs">{{ old('questions_json', $existingQuestions->toJson(JSON_PRETTY_PRINT)) }}</textarea>
        </div>
        @include('admin.partials.seo-fields', ['item' => $quiz])


        <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 font-semibold text-white hover:bg-brand-700">Save</button>
    </form>
</x-admin-layout>
