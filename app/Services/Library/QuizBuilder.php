<?php

namespace App\Services\Library;

use App\Content\QuizLibrary;
use App\Content\QuizTopics;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\TriviaQuestion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns approved Question Bank rows into quizzes (10 questions each), saved IN REVIEW.
 * Only questions with an explanation are used, so every quiz explains its answers.
 */
class QuizBuilder
{
    public const SIZE = 10;

    /** @return Quiz[] quizzes created */
    public function buildAll(int $max = 5): array
    {
        $made = [];
        foreach (array_keys(QuizTopics::ALL) as $topic) {
            while (count($made) < $max && ($quiz = $this->buildOne($topic))) {
                $made[] = $quiz;
            }
        }

        return $made;
    }

    public function available(string $topic): \Illuminate\Database\Eloquent\Builder
    {
        return TriviaQuestion::where('topic', $topic)->where('status', 'approved')->whereNull('quiz_id')
            ->whereNotNull('explanation')->where('explanation', '!=', '');
    }

    public function buildOne(string $topic): ?Quiz
    {
        $questions = $this->available($topic)->orderBy('id')->limit(self::SIZE)->get();
        if ($questions->count() < self::SIZE) {
            return null;
        }
        $n = Quiz::where('topic', $topic)->count() + 1;
        $label = QuizTopics::label($topic);

        return $this->create([
            'slug' => $this->uniqueSlug(Str::slug($label).'-quiz-'.$n),
            'title' => "{$label} Quiz {$n}: ".self::SIZE.' Questions With Answers Explained',
            'topic' => $topic,
            'intro' => QuizTopics::ALL[$topic]['intro'],
        ], $questions);
    }

    /** Shared with TriviaStarterLoader. $questions are saved TriviaQuestion rows. */
    public function create(array $fields, Collection $questions): Quiz
    {
        return DB::transaction(function () use ($fields, $questions) {
            $category = Category::where('slug', 'quizzes')->firstOrFail();
            $difficulty = $questions->countBy('difficulty')->sortDesc()->keys()->first() ?? 'medium';
            $sources = $questions->pluck('source')->unique()->map(fn ($s) => QuizTopics::CREDITS[$s] ?? null)->filter()->implode("\n");

            $quiz = Quiz::create($fields + [
                'category_id' => $category->id, 'difficulty' => $difficulty, 'sources' => $sources,
                'language' => 'en', 'status' => 'in_review', 'reviewed_at' => now()->toDateString(),
            ]);
            foreach ($questions->values() as $i => $tq) {
                $q = $quiz->questions()->create(['position' => $i + 1, 'prompt' => $tq->prompt, 'explanation' => $tq->explanation]);
                foreach ($tq->answerPairs() as $j => [$label, $correct]) {
                    $q->answers()->create(['position' => $j + 1, 'label' => $label, 'is_correct' => $correct]);
                }
                $tq->update(['quiz_id' => $quiz->id]);
            }

            return $quiz;
        });
    }

    private function uniqueSlug(string $slug): string
    {
        $base = $slug;
        for ($i = 2; Quiz::where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
