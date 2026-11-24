<?php

namespace App\Http\Controllers;

use App\Content\QuizTopics;
use App\Models\Quiz;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $query = Quiz::where('status', 'published')->withCount('questions');
        $difficulty = $request->query('level');
        if (is_string($difficulty) && isset(Quiz::DIFFICULTIES[$difficulty])) {
            $query->where('difficulty', $difficulty);
        } else {
            $difficulty = null;
        }
        $quizzes = $query->latest('published_at')->latest('id')->paginate(24)->withQueryString();

        return view('quizzes.index', ['quizzes' => $quizzes, 'topics' => $this->topicCounts(), 'level' => $difficulty]);
    }

    /** /quizzes/topic/{topic}: one page per subject, indexable once it has 2+ quizzes. */
    public function topic(string $topic)
    {
        abort_unless(QuizTopics::exists($topic), 404);
        $quizzes = Quiz::where('status', 'published')->where('topic', $topic)->withCount('questions')
            ->orderByRaw("CASE difficulty WHEN 'easy' THEN 1 WHEN 'medium' THEN 2 WHEN 'hard' THEN 3 ELSE 4 END")->orderBy('id')->get();
        abort_if($quizzes->isEmpty(), 404);

        return view('quizzes.topic', ['topic' => $topic, 'info' => QuizTopics::ALL[$topic], 'quizzes' => $quizzes, 'topics' => $this->topicCounts()]);
    }

    /** topic => published quiz count, only topics that have quizzes. */
    public static function topicCounts(): array
    {
        $counts = Quiz::where('status', 'published')->whereNotNull('topic')->selectRaw('topic, count(*) c')->groupBy('topic')->pluck('c', 'topic');

        return collect(QuizTopics::ALL)->keys()->filter(fn ($t) => ($counts[$t] ?? 0) > 0)->mapWithKeys(fn ($t) => [$t => (int) $counts[$t]])->all();
    }

    public function show(Request $request, Quiz $quiz)
    {
        // Moderators can open an In review quiz to check it before publishing.
        $preview = $quiz->status !== 'published';
        abort_if($preview && ! $request->user()?->canModerate(), 404);
        if (! $preview) {
            \App\Models\ContentView::track($quiz);
        }

        $quiz->load('questions.answers');

        return view('quizzes.show', ['quiz' => $quiz, 'preview' => $preview]);
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
        $isTrivia = $quiz->isTrivia();

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
            $outcomeLabel = $quiz->outcomeFor($percentage)['label'] ?? match (true) {
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
