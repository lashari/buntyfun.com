<?php

namespace App\Http\Controllers\Admin;

use App\Content\QuizTopics;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\TriviaQuestion;
use App\Services\Library\QuizBuilder;
use App\Services\Library\TriviaImporter;
use App\Services\Library\TriviaStarterLoader;
use App\Services\Library\TriviaValidator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuestionBankController extends Controller
{
    public function index(Request $request, QuizBuilder $builder)
    {
        $status = $request->query('status', 'in_review');
        $topic = $request->query('topic');
        $q = TriviaQuestion::query()
            ->when(isset(TriviaQuestion::STATUSES[$status]), fn ($w) => $w->where('status', $status))
            ->when(QuizTopics::exists($topic), fn ($w) => $w->where('topic', $topic))
            ->when($request->boolean('flagged'), fn ($w) => $w->whereNotNull('flags'))
            ->when($request->boolean('no_explanation'), fn ($w) => $w->where(fn ($x) => $x->whereNull('explanation')->orWhere('explanation', '')))
            ->when($s = trim((string) $request->query('q')), fn ($w) => $w->where('prompt', 'like', '%'.$s.'%'));

        $counts = TriviaQuestion::selectRaw('topic, status, count(*) c')->groupBy('topic', 'status')->get()
            ->groupBy('topic')->map(fn ($rows) => $rows->pluck('c', 'status'));
        $ready = collect(array_keys(QuizTopics::ALL))->mapWithKeys(fn ($t) => [$t => $builder->available($t)->count()]);

        return view('admin.question-bank.index', [
            'questions' => $q->orderBy('id')->paginate(25)->withQueryString(),
            'status' => $status, 'topic' => $topic, 'counts' => $counts, 'ready' => $ready,
        ]);
    }

    public function import(Request $request, TriviaImporter $importer)
    {
        $data = $request->validate([
            'source' => ['required', Rule::in(['opentdb', 'opentriviaqa'])],
            'topic' => ['required', Rule::in(array_keys(QuizTopics::ALL))],
            'amount' => ['required', 'integer', 'min:1', 'max:50'],
        ]);
        try {
            $s = $data['source'] === 'opentdb'
                ? $importer->fromOpenTdb($data['topic'], $data['amount'])
                : $importer->fromOpenTriviaQa($data['topic'], $data['amount']);
        } catch (\Throwable $e) {
            return back()->withErrors(['import' => 'Import failed: '.mb_substr($e->getMessage(), 0, 200)]);
        }
        AuditLog::record($request->user(), 'imported', 'trivia_question', 0);

        return back()->with('status', "Imported {$s['saved']} new question(s) as In review. {$s['duplicates']} duplicate(s) skipped, {$s['rejected']} rejected.")
            ->with('reasons', array_slice($s['reasons'], 0, 15));
    }

    public function starter(Request $request, TriviaStarterLoader $loader)
    {
        abort_unless($request->user()->canPublish(), 403);
        $s = $loader->load($request->user()->id);
        AuditLog::record($request->user(), 'loaded_starter', 'quiz', 0);

        return back()->with('status', "Starter pack: {$s['quizzes']} quiz(zes) created In review with {$s['questions']} reviewed questions ({$s['skipped']} already existed). Open Admin → Quizzes to preview and publish.");
    }

    public function build(Request $request, QuizBuilder $builder)
    {
        $made = $builder->buildAll(10);

        return back()->with('status', $made ? count($made).' quiz(zes) built and saved In review: '.collect($made)->pluck('title')->implode('; ') : 'No topic has 10 approved, unused questions with explanations yet.');
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'max:100'], 'ids.*' => ['integer'],
            'action' => ['required', Rule::in(['approve', 'reject', 'review'])],
        ]);
        abort_if($data['action'] === 'approve' && ! $request->user()->canModerate(), 403);
        $rows = TriviaQuestion::whereIn('id', $data['ids'])->whereNull('quiz_id')->get();
        $skipped = 0;
        foreach ($rows as $row) {
            if ($data['action'] === 'approve' && blank($row->explanation)) {
                $skipped++;   // approving needs an explanation, so every quiz answer is explained
                continue;
            }
            $row->update([
                'status' => ['approve' => 'approved', 'reject' => 'rejected', 'review' => 'in_review'][$data['action']],
                'reviewed_by' => $request->user()->id, 'reviewed_at' => now(),
            ]);
        }
        AuditLog::record($request->user(), 'bulk_'.$data['action'], 'trivia_question', $rows->count());

        return back()->with('status', ($rows->count() - $skipped).' question(s) updated.'.($skipped ? " {$skipped} skipped: add an explanation before approving." : ''));
    }

    public function edit(TriviaQuestion $question)
    {
        return view('admin.question-bank.edit', ['question' => $question]);
    }

    public function update(Request $request, TriviaQuestion $question, TriviaValidator $validator)
    {
        $data = $request->validate([
            'topic' => ['required', Rule::in(array_keys(QuizTopics::ALL))],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
            'prompt' => ['required', 'string', 'max:300'],
            'correct_answer' => ['required', 'string', 'max:120'],
            'wrong' => ['required', 'array', 'size:3'], 'wrong.*' => ['required', 'string', 'max:120'],
            'explanation' => ['nullable', 'string', 'max:600'],
            'status' => ['required', Rule::in(array_keys(TriviaQuestion::STATUSES))],
        ]);
        $wrong = array_values(array_map('trim', $data['wrong']));
        [$reject] = $validator->check(trim($data['prompt']), trim($data['correct_answer']), $wrong);
        if ($reject && $data['status'] === 'approved') {
            return back()->withInput()->withErrors(['prompt' => $reject]);
        }
        if ($data['status'] === 'approved' && blank($data['explanation'])) {
            return back()->withInput()->withErrors(['explanation' => 'Write a short explanation before approving.']);
        }
        $hash = TriviaQuestion::hashFor($data['prompt'], $data['correct_answer']);
        if (TriviaQuestion::where('hash', $hash)->whereKeyNot($question->id)->exists()) {
            return back()->withInput()->withErrors(['prompt' => 'Another question in the bank is the same.']);
        }
        $question->update([
            'topic' => $data['topic'], 'difficulty' => $data['difficulty'], 'prompt' => trim($data['prompt']),
            'correct_answer' => trim($data['correct_answer']), 'wrong_answers' => $wrong, 'hash' => $hash,
            'explanation' => $data['explanation'] ? trim($data['explanation']) : null, 'status' => $data['status'],
            'reviewed_by' => $request->user()->id, 'reviewed_at' => now(),
        ]);
        AuditLog::record($request->user(), 'updated', 'trivia_question', $question->id);

        $next = TriviaQuestion::where('status', 'in_review')->where('id', '>', $question->id)->orderBy('id')->first();

        return $next && $request->boolean('next')
            ? redirect()->route('admin.question-bank.edit', $next)->with('status', 'Saved. Next question:')
            : redirect()->route('admin.question-bank.index')->with('status', 'Question saved.');
    }
}
