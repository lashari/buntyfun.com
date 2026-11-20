<?php

namespace Database\Seeders;

use App\Content\QuizLibrary;
use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

/** Insert-only: never touches a quiz that already exists (safe to re-run). */
class QuizLibrarySeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::where('slug', 'quizzes')->first();
        if (! $category) {
            return;
        }

        foreach (QuizLibrary::QUIZZES as $data) {
            if (Quiz::where('slug', $data['slug'])->exists()) {
                continue;
            }
            $quiz = Quiz::create([
                'category_id' => $category->id, 'slug' => $data['slug'], 'title' => $data['title'],
                'intro' => $data['intro'], 'language' => 'en', 'status' => 'published', 'published_at' => now(),
            ]);
            foreach ($data['questions'] as $i => [$prompt, $answers, $explanation]) {
                $question = $quiz->questions()->create(['position' => $i + 1, 'prompt' => $prompt, 'explanation' => $explanation]);
                foreach ($answers as $j => [$label, $correct]) {
                    $question->answers()->create(['position' => $j + 1, 'label' => $label, 'is_correct' => $correct]);
                }
            }
        }
    }
}
