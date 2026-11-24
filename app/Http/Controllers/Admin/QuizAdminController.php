<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesPublishing;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizAdminController extends Controller
{
    use HandlesPublishing;
    use \App\Http\Controllers\Admin\Concerns\SavesContentMeta;

    public function index(Request $request)
    {
        $status = $request->query('status');
        $quizzes = Quiz::with('category')->withCount('questions')
            ->when(in_array($status, ['draft', 'in_review', 'published'], true), fn ($q) => $q->where('status', $status))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.quizzes.index', ['quizzes' => $quizzes, 'status' => $status]);
    }

    public function create()
    {
        return view('admin.quizzes.form', ['quiz' => new Quiz, 'categories' => Category::where('department', 'quizzes')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $quiz = Quiz::create($data);
        $this->syncQuestions($quiz, $request->input('questions_json'));

        AuditLog::record($request->user(), 'created', 'quiz', $quiz->id);
        $this->saveMeta($request, $quiz);

        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz saved.');
    }

    public function edit(Quiz $quiz)
    {
        $quiz->load('questions.answers');

        return view('admin.quizzes.form', ['quiz' => $quiz, 'categories' => Category::where('department', 'quizzes')->get()]);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $data = $this->validated($request);
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $quiz->update($data);
        $this->syncQuestions($quiz, $request->input('questions_json'));

        AuditLog::record($request->user(), 'updated', 'quiz', $quiz->id);
        $this->saveMeta($request, $quiz);

        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz saved.');
    }

    public function destroy(Request $request, Quiz $quiz)
    {
        $this->authorizeDelete($request);

        AuditLog::record($request->user(), 'deleted', 'quiz', $quiz->id);
        $quiz->delete();

        return redirect()->route('admin.quizzes.index')->with('status', 'Quiz deleted.');
    }

    public function publish(Request $request, Quiz $quiz)
    {
        $this->publishModel($request, $quiz, 'quiz');

        return back()->with('status', 'Quiz published.');
    }

    public function unpublish(Request $request, Quiz $quiz)
    {
        $this->unpublishModel($request, $quiz, 'quiz');

        return back()->with('status', 'Quiz unpublished.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'topic' => ['nullable', 'in:'.implode(',', array_keys(\App\Content\QuizTopics::ALL))],
            'title' => ['required', 'string', 'max:255'],
            'intro' => ['nullable', 'string', 'max:2000'],
            'difficulty' => ['nullable', 'in:'.implode(',', array_keys(Quiz::DIFFICULTIES))],
            'sources' => ['nullable', 'string', 'max:2000'],
            'reviewed_at' => ['nullable', 'date'],
            'outcomes_json' => ['nullable', 'json'],
            'slug' => ['nullable', 'string', 'max:255'],
            'language' => ['required', 'in:en,ur'],
            'status' => ['required', 'in:draft,in_review,published'],
        ]);

        // outcomes_json: [{min: 0..1, label, text}] for score-based (personality) quizzes.
        $outcomes = json_decode($data['outcomes_json'] ?? '', true);
        $data['outcomes'] = is_array($outcomes) && $outcomes ? array_values(array_filter($outcomes, fn ($o) => is_array($o) && isset($o['label']))) : null;
        unset($data['outcomes_json']);

        return $data;
    }

    /**
     * questions_json: [{prompt, explanation?, answers: [{label, is_correct?, score_value?}]}]
     * Simplest way to manage a variable number of questions/answers without a
     * separate nested-resource CRUD for Release 1 scope.
     */
    private function syncQuestions(Quiz $quiz, ?string $questionsJson): void
    {
        if (! $questionsJson) {
            return;
        }

        $questions = json_decode($questionsJson, true);
        if (! is_array($questions)) {
            return;
        }

        $quiz->questions()->each(function ($question) {
            $question->answers()->delete();
            $question->delete();
        });

        foreach ($questions as $position => $question) {
            $createdQuestion = $quiz->questions()->create([
                'position' => $position + 1,
                'prompt' => $question['prompt'] ?? '',
                'explanation' => $question['explanation'] ?? null,
            ]);

            foreach (($question['answers'] ?? []) as $answerPosition => $answer) {
                $createdQuestion->answers()->create([
                    'position' => $answerPosition + 1,
                    'label' => $answer['label'] ?? '',
                    'is_correct' => array_key_exists('is_correct', $answer) ? (bool) $answer['is_correct'] : null,
                    'score_value' => $answer['score_value'] ?? null,
                ]);
            }
        }
    }
}
