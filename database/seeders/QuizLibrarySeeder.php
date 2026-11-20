<?php

namespace Database\Seeders;

use App\Content\QuizLibrary;
use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

/**
 * Safe to re-run. Inserts library quizzes that are missing; for a quiz that already exists it
 * only fills empty fields (intro, difficulty, sources, outcomes, review date) and never edits
 * an admin's questions, except that a quiz with fewer than 5 questions (the old demo) is rebuilt.
 */
class QuizLibrarySeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::where('slug', 'quizzes')->first();
        if (! $category) {
            return;
        }

        foreach (QuizLibrary::QUIZZES as $data) {
            $quiz = Quiz::where('slug', $data['slug'])->first();
            $rebuild = $quiz && $quiz->questions()->count() < 5;

            if (! $quiz) {
                $quiz = Quiz::create([
                    'category_id' => $category->id, 'slug' => $data['slug'], 'title' => $data['title'],
                    'language' => 'en', 'status' => 'published', 'published_at' => now(),
                ]);
            } elseif ($rebuild) {
                $quiz->questions()->delete();
                $quiz->fill(['title' => $data['title'], 'status' => 'published', 'published_at' => $quiz->published_at ?? now()]);
                $quiz->intro = null;
            }

            foreach (['intro' => 'intro', 'difficulty' => 'difficulty', 'sources' => 'sources', 'outcomes' => 'outcomes', 'reviewed_at' => 'reviewed'] as $column => $key) {
                if (blank($quiz->{$column}) && ! blank($data[$key] ?? null)) {
                    $quiz->{$column} = $data[$key];
                }
            }
            $quiz->save();

            $personality = ($data['type'] ?? 'trivia') === 'personality';
            if ($quiz->questions()->exists()) {
                $this->reorderUnchanged($quiz, $data['questions'], $personality);

                continue;
            }
            foreach ($data['questions'] as $i => [$prompt, $answers, $explanation]) {
                $question = $quiz->questions()->create(['position' => $i + 1, 'prompt' => $prompt, 'explanation' => $explanation]);
                foreach (QuizLibrary::orderedAnswers($prompt, $answers, $personality) as $j => [$label, $value]) {
                    $question->answers()->create($personality
                        ? ['position' => $j + 1, 'label' => $label, 'score_value' => $value]
                        : ['position' => $j + 1, 'label' => $label, 'is_correct' => $value]);
                }
            }
        }
    }

    /** Apply the fair option order to questions that still match the library exactly (same prompt and options). */
    private function reorderUnchanged(Quiz $quiz, array $questions, bool $personality): void
    {
        $existing = $quiz->questions()->with('answers')->get()->keyBy('prompt');
        foreach ($questions as [$prompt, $answers]) {
            $question = $existing->get($prompt);
            $labels = array_column($answers, 0);
            if (! $question || $question->answers->pluck('label')->sort()->values()->all() !== collect($labels)->sort()->values()->all()) {
                continue;
            }
            foreach (QuizLibrary::orderedAnswers($prompt, $answers, $personality) as $j => [$label]) {
                $question->answers->firstWhere('label', $label)->update(['position' => $j + 1]);
            }
        }
    }
}
