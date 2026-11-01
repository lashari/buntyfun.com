<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::where('status', 'published')->latest()->paginate(12);

        return view('quizzes.index', ['quizzes' => $quizzes]);
    }

    public function show(Quiz $quiz)
    {
        abort_unless($quiz->status === 'published', 404);

        $quiz->load('questions.answers');

        return view('quizzes.show', ['quiz' => $quiz]);
    }

    public function submit(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->status === 'published', 404);

        $quiz->load('questions.answers');

        $data = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'integer'],
        ]);

        $submittedAnswerIds = $data['answers'];
        $isTrivia = $quiz->questions->flatMap->answers->contains(fn ($a) => ! is_null($a->is_correct));

        if ($isTrivia) {
            $correct = 0;
            foreach ($quiz->questions as $question) {
                $answerId = $submittedAnswerIds[$question->id] ?? null;
                $answer = $question->answers->firstWhere('id', (int) $answerId);
                if ($answer && $answer->is_correct) {
                    $correct++;
                }
            }
            $total = $quiz->questions->count();
            $outcomeLabel = "You scored {$correct}/{$total}!";
        } else {
            $score = 0;
            $maxScore = 0;
            foreach ($quiz->questions as $question) {
                $answerId = $submittedAnswerIds[$question->id] ?? null;
                $answer = $question->answers->firstWhere('id', (int) $answerId);
                $score += $answer?->score_value ?? 0;
                $maxScore += $question->answers->max('score_value') ?? 0;
            }
            $percentage = $maxScore > 0 ? $score / $maxScore : 0;
            $outcomeLabel = match (true) {
                $percentage >= 0.75 => 'Best Friends Forever',
                $percentage >= 0.4 => 'Good Friends',
                default => 'Still Getting to Know Each Other',
            };
        }

        $result = QuizResult::create([
            'quiz_id' => $quiz->id,
            'result_slug' => Str::random(10),
            'answers_json' => $submittedAnswerIds,
            'outcome_label' => $outcomeLabel,
        ]);

        return redirect()->route('quizzes.result', ['quiz' => $quiz->slug, 'result' => $result->result_slug]);
    }

    public function result(Quiz $quiz, QuizResult $result)
    {
        abort_unless($result->quiz_id === $quiz->id, 404);

        return view('quizzes.result', ['quiz' => $quiz, 'result' => $result]);
    }
}
