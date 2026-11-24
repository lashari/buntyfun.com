<x-admin-layout title="Question Bank">
    <h1 class="text-2xl font-bold">Question Bank</h1>
    <p class="mt-1 text-sm text-slate-600">Imported trivia waits here In review. Approve a question (it needs an explanation), and every 10 approved questions in a topic become a new quiz, saved In review for you to publish. Sources: Open Trivia DB and OpenTriviaQA, both CC BY-SA 4.0; credit lines are added to each quiz automatically.</p>

    @if (session('status'))<p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-800">{{ session('status') }}</p>@endif
    @if (session('reasons'))<ul class="mt-2 list-disc rounded-lg bg-amber-50 px-8 py-2 text-xs text-amber-800">@foreach (session('reasons') as $r)<li>{{ $r }}</li>@endforeach</ul>@endif
    @error('import')<p class="mt-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700">{{ $message }}</p>@enderror

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <form method="POST" action="{{ route('admin.question-bank.import') }}" class="rounded-xl border border-slate-200 bg-white p-4 text-sm">
            @csrf
            <h2 class="font-semibold">Import questions</h2>
            <label class="mt-3 block">Source
                <select name="source" class="mt-1 w-full rounded border-slate-300"><option value="opentdb">Open Trivia DB (verified only)</option><option value="opentriviaqa">OpenTriviaQA</option></select></label>
            <label class="mt-2 block">Topic
                <select name="topic" class="mt-1 w-full rounded border-slate-300">@foreach (\App\Content\QuizTopics::ALL as $k => $t)<option value="{{ $k }}">{{ $t['label'] }}</option>@endforeach</select></label>
            <label class="mt-2 block">How many <input type="number" name="amount" value="10" min="1" max="50" class="mt-1 w-full rounded border-slate-300"></label>
            <button class="mt-3 w-full rounded-lg bg-brand-600 py-2 font-semibold text-white">Import as In review</button>
            <p class="mt-2 text-xs text-slate-500">Maths and Computers exist only on Open Trivia DB. Wait 5 seconds between Open Trivia DB imports.</p>
        </form>
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm">
            <h2 class="font-semibold">Starter pack: 100 reviewed questions</h2>
            <p class="mt-1 text-slate-600">10 quizzes × 10 questions picked and fact-checked by hand, each with an explanation. Creates the quizzes In review. Safe to press twice.</p>
            <form method="POST" action="{{ route('admin.question-bank.starter') }}" class="mt-3">@csrf<button class="w-full rounded-lg bg-navy-600 py-2 font-semibold text-white">Load starter pack</button></form>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm">
            <h2 class="font-semibold">Ready to become quizzes</h2>
            <ul class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1">
                @foreach (\App\Content\QuizTopics::ALL as $k => $t)
                    <li class="flex justify-between"><span>{{ $t['label'] }}</span><span class="{{ $ready[$k] >= 10 ? 'font-bold text-emerald-700' : 'text-slate-500' }}">{{ $ready[$k] }}/10</span></li>
                @endforeach
            </ul>
            <form method="POST" action="{{ route('admin.question-bank.build') }}" class="mt-3">@csrf<button class="w-full rounded-lg border border-slate-300 py-2 font-semibold">Build quizzes now</button></form>
            <p class="mt-2 text-xs text-slate-500">The daily automation does this at 01:30 too.</p>
        </div>
    </div>

    <form method="GET" class="mt-6 flex flex-wrap items-end gap-2 text-sm">
        <select name="status" class="rounded border-slate-300">@foreach (\App\Models\TriviaQuestion::STATUSES + ['all' => 'All'] as $k => $l)<option value="{{ $k }}" @selected($status === $k)>{{ $l }}</option>@endforeach</select>
        <select name="topic" class="rounded border-slate-300"><option value="">All topics</option>@foreach (\App\Content\QuizTopics::ALL as $k => $t)<option value="{{ $k }}" @selected($topic === $k)>{{ $t['label'] }} ({{ $counts[$k]['in_review'] ?? 0 }} to review)</option>@endforeach</select>
        <label><input type="checkbox" name="flagged" value="1" @checked(request('flagged'))> Flagged only</label>
        <label><input type="checkbox" name="no_explanation" value="1" @checked(request('no_explanation'))> Missing explanation</label>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search" class="rounded border-slate-300">
        <button class="rounded-lg bg-slate-800 px-4 py-2 font-semibold text-white">Filter</button>
    </form>

    <form method="POST" action="{{ route('admin.question-bank.bulk') }}" x-data="{ all: false }">
        @csrf
        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <button name="action" value="approve" class="rounded-lg bg-emerald-600 px-3 py-1.5 font-semibold text-white">Approve selected</button>
            <button name="action" value="reject" class="rounded-lg bg-red-600 px-3 py-1.5 font-semibold text-white">Reject selected</button>
            <button name="action" value="review" class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold">Back to review</button>
        </div>
        <table class="mt-3 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr><th class="px-3 py-2"><input type="checkbox" @change="document.querySelectorAll('.qb-row').forEach(c => c.checked = $event.target.checked)"></th><th class="px-3 py-2">Question</th><th class="px-3 py-2">Topic</th><th class="px-3 py-2">Status</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($questions as $q)
                    <tr class="align-top">
                        <td class="px-3 py-2">@unless ($q->quiz_id)<input type="checkbox" name="ids[]" value="{{ $q->id }}" class="qb-row">@endunless</td>
                        <td class="px-3 py-2">
                            <p class="font-medium">{{ $q->prompt }}</p>
                            <p class="mt-1 text-xs"><span class="font-semibold text-emerald-700">✓ {{ $q->correct_answer }}</span> <span class="text-slate-500">· ✗ {{ implode(' · ✗ ', $q->wrong_answers ?? []) }}</span></p>
                            @if ($q->explanation)<p class="mt-1 text-xs text-slate-600">{{ Str::limit($q->explanation, 160) }}</p>@else<p class="mt-1 text-xs font-semibold text-amber-700">No explanation yet</p>@endif
                            @foreach ($q->flags ?? [] as $f)<span class="mt-1 mr-1 inline-block rounded bg-amber-100 px-1.5 py-0.5 text-xs text-amber-800">⚠ {{ $f }}</span>@endforeach
                        </td>
                        <td class="px-3 py-2 text-slate-600">{{ \App\Content\QuizTopics::label($q->topic) }}<br><span class="text-xs">{{ $q->difficulty }} · {{ $q->source }}</span></td>
                        <td class="px-3 py-2"><span class="rounded-full px-2 py-0.5 text-xs {{ ['approved' => 'bg-emerald-100 text-emerald-700', 'rejected' => 'bg-red-100 text-red-700'][$q->status] ?? 'bg-slate-100 text-slate-600' }}">{{ \App\Models\TriviaQuestion::STATUSES[$q->status] }}</span>
                            @if ($q->quiz_id)<br><a href="{{ route('admin.quizzes.edit', $q->quiz_id) }}" class="text-xs text-navy-600 underline">in quiz #{{ $q->quiz_id }}</a>@endif</td>
                        <td class="px-3 py-2 text-right"><a href="{{ route('admin.question-bank.edit', $q) }}" class="text-navy-600 hover:underline">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">Nothing here. Import questions or load the starter pack above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </form>
    <div class="mt-4">{{ $questions->links() }}</div>
</x-admin-layout>
