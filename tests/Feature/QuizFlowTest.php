<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_unpublished_quiz_returns_404(): void
    {
        $quiz = $this->makeQuiz('draft');

        $this->get(route('quizzes.show', $quiz))->assertStatus(404);
    }

    public function test_published_quiz_can_be_taken_and_scored(): void
    {
        $quiz = $this->makeQuiz('published');
        $question = $quiz->questions()->create(['position' => 1, 'prompt' => 'Pick one']);
        $low = $question->answers()->create(['position' => 1, 'label' => 'Low', 'score_value' => 1]);
        $high = $question->answers()->create(['position' => 2, 'label' => 'High', 'score_value' => 2]);

        $this->get(route('quizzes.show', $quiz))->assertOk();

        $response = $this->post(route('quizzes.submit', $quiz), [
            'answers' => [$question->id => $high->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quiz_results', ['quiz_id' => $quiz->id]);

        $response->assertRedirectContains('/result/');
    }

    private function makeQuiz(string $status): Quiz
    {
        $category = Category::create(['department' => 'quizzes', 'slug' => 'quizzes', 'name' => 'Quizzes']);

        return Quiz::create([
            'category_id' => $category->id,
            'slug' => 'test-quiz',
            'title' => 'Test Quiz',
            'language' => 'en',
            'status' => $status,
        ]);
    }
}
