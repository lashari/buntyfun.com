<x-admin-layout title="Edit question">
    <a href="{{ route('admin.question-bank.index') }}" class="text-sm text-navy-600 underline">← Question Bank</a>
    <h1 class="mt-2 text-2xl font-bold">Edit question #{{ $question->id }}</h1>
    @if (session('status'))<p class="mt-3 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-800">{{ session('status') }}</p>@endif
    @foreach ($question->flags ?? [] as $f)<p class="mt-2 rounded bg-amber-50 px-3 py-1.5 text-sm text-amber-800">⚠ {{ $f }}</p>@endforeach
    <p class="mt-2 text-xs text-slate-500">Source: {{ $question->source }} {{ $question->source_ref }} @if ($question->quiz_id)· used in quiz #{{ $question->quiz_id }} (edit the quiz itself to change what visitors see)@endif</p>

    <form method="POST" action="{{ route('admin.question-bank.update', $question) }}" class="mt-4 max-w-3xl space-y-3 rounded-xl border border-slate-200 bg-white p-5 text-sm">
        @csrf @method('PUT')
        @if ($errors->any())<ul class="rounded bg-red-50 px-6 py-2 text-red-700">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>@endif
        <label class="block font-semibold">Question<textarea name="prompt" rows="2" class="mt-1 w-full rounded border-slate-300">{{ old('prompt', $question->prompt) }}</textarea></label>
        <label class="block font-semibold text-emerald-700">Correct answer<input name="correct_answer" value="{{ old('correct_answer', $question->correct_answer) }}" class="mt-1 w-full rounded border-slate-300"></label>
        @foreach (range(0, 2) as $i)
            <label class="block font-semibold text-red-700">Wrong answer {{ $i + 1 }}<input name="wrong[]" value="{{ old('wrong.'.$i, $question->wrong_answers[$i] ?? '') }}" class="mt-1 w-full rounded border-slate-300"></label>
        @endforeach
        <label class="block font-semibold">Explanation <span class="font-normal text-slate-500">(1–2 sentences: why the answer is right, plus one extra fact. Required to approve.)</span>
            <textarea name="explanation" rows="3" maxlength="600" class="mt-1 w-full rounded border-slate-300">{{ old('explanation', $question->explanation) }}</textarea></label>
        <div class="flex flex-wrap gap-4">
            <label>Topic <select name="topic" class="rounded border-slate-300">@foreach (\App\Content\QuizTopics::ALL as $k => $t)<option value="{{ $k }}" @selected(old('topic', $question->topic) === $k)>{{ $t['label'] }}</option>@endforeach</select></label>
            <label>Difficulty <select name="difficulty" class="rounded border-slate-300">@foreach (['easy', 'medium', 'hard'] as $d)<option @selected(old('difficulty', $question->difficulty) === $d)>{{ $d }}</option>@endforeach</select></label>
            <label>Status <select name="status" class="rounded border-slate-300">@foreach (\App\Models\TriviaQuestion::STATUSES as $k => $l)<option value="{{ $k }}" @selected(old('status', $question->status === 'in_review' ? 'approved' : $question->status) === $k)>{{ $l }}</option>@endforeach</select></label>
        </div>
        <div class="flex gap-2 pt-2">
            <button name="next" value="1" class="rounded-lg bg-brand-600 px-4 py-2 font-semibold text-white">Save &amp; next in review</button>
            <button class="rounded-lg border border-slate-300 px-4 py-2 font-semibold">Save</button>
        </div>
    </form>
</x-admin-layout>
