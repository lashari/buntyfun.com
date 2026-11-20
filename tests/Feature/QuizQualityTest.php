<?php

namespace Tests\Feature;

use App\Content\QuizLibrary;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\User;
use Database\Seeders\QuizLibrarySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizQualityTest extends TestCase
{
    use RefreshDatabase;

    private function category(): Category
    {
        return Category::firstOrCreate(['slug' => 'quizzes'], ['department' => 'quizzes', 'name' => 'Quizzes']);
    }

    public function test_library_data_is_complete(): void
    {
        $slugs = [];
        foreach (QuizLibrary::QUIZZES as $quiz) {
            $slugs[] = $quiz['slug'];
            $personality = ($quiz['type'] ?? 'trivia') === 'personality';
            $this->assertCount(10, $quiz['questions'], $quiz['slug']);
            $this->assertArrayHasKey($quiz['difficulty'], Quiz::DIFFICULTIES, $quiz['slug']);
            $this->assertGreaterThan(150, strlen($quiz['intro']), $quiz['slug']);
            $this->assertNotEmpty($quiz['reviewed']);
            if ($personality) {
                $this->assertCount(4, $quiz['outcomes']);
                $this->assertSame(0, end($quiz['outcomes'])['min'], 'lowest outcome must start at 0');
            } else {
                $this->assertNotEmpty($quiz['sources'], $quiz['slug']);
            }
            foreach ($quiz['questions'] as [$prompt, $answers, $explanation]) {
                $this->assertCount(4, $answers, $prompt);
                $this->assertCount(4, array_unique(array_column($answers, 0)), "duplicate option: {$prompt}");
                if (! $personality) {
                    $this->assertCount(1, array_filter($answers, fn ($a) => $a[1] === true), "one right answer: {$prompt}");
                    $this->assertNotEmpty($explanation, $prompt);
                }
            }
        }
        $this->assertSame($slugs, array_unique($slugs));

        // the right answer is spread across A-D, not always in the same slot
        $slots = [0, 0, 0, 0];
        foreach (QuizLibrary::QUIZZES as $quiz) {
            if (($quiz['type'] ?? 'trivia') === 'personality') {
                continue;
            }
            foreach ($quiz['questions'] as [$prompt, $answers]) {
                $slots[array_search(true, array_column(QuizLibrary::orderedAnswers($prompt, $answers), 1), true)]++;
            }
        }
        $this->assertGreaterThanOrEqual(10, min($slots), json_encode($slots));
        $this->assertLessThanOrEqual(40, max($slots), json_encode($slots));
    }

    public function test_live_library_quiz_options_are_reordered_without_changing_ids(): void
    {
        $cat = $this->category();
        [$prompt, $answers, $explanation] = QuizLibrary::QUIZZES[0]['questions'][0];
        $quiz = Quiz::create(['category_id' => $cat->id, 'slug' => QuizLibrary::QUIZZES[0]['slug'], 'title' => 'T', 'language' => 'en', 'status' => 'published']);
        foreach (range(1, 5) as $i) {
            $q = $quiz->questions()->create(['position' => $i, 'prompt' => $i === 1 ? $prompt : "Admin {$i}"]);
            foreach ($answers as $j => [$label, $correct]) {
                $q->answers()->create(['position' => $j + 1, 'label' => $label, 'is_correct' => $correct]);
            }
        }
        $ids = $quiz->questions()->first()->answers()->pluck('id')->sort()->values();

        $this->seed(QuizLibrarySeeder::class);

        $now = $quiz->questions()->first()->answers()->get();
        $this->assertSame(array_column(QuizLibrary::orderedAnswers($prompt, $answers), 0), $now->pluck('label')->all());
        $this->assertEquals($ids, $now->pluck('id')->sort()->values());
    }

    public function test_old_short_demo_quiz_is_rebuilt_but_admin_quizzes_are_left_alone(): void
    {
        $cat = $this->category();
        $demo = Quiz::create(['category_id' => $cat->id, 'slug' => 'how-well-do-you-know-your-best-friend', 'title' => 'How Well Do You Know Your Best Friend?', 'language' => 'en', 'status' => 'published', 'published_at' => now()->subMonth()]);
        $q = $demo->questions()->create(['position' => 1, 'prompt' => 'Old question?']);
        $q->answers()->create(['position' => 1, 'label' => 'Pizza', 'score_value' => 1]);

        // An admin-edited library quiz with its own intro must keep it.
        $solar = Quiz::create(['category_id' => $cat->id, 'slug' => 'solar-system-quiz', 'title' => 'My own title', 'intro' => 'Admin intro', 'language' => 'en', 'status' => 'published']);
        foreach (range(1, 6) as $i) {
            $solar->questions()->create(['position' => $i, 'prompt' => "Admin question {$i}"]);
        }

        $this->seed(QuizLibrarySeeder::class);
        $this->seed(QuizLibrarySeeder::class);

        $demo->refresh();
        $this->assertSame(10, $demo->questions()->count());
        $this->assertStringContainsString('10-Question Friendship Quiz', $demo->title);
        $this->assertCount(4, $demo->outcomes);
        $this->assertSame('easy', $demo->difficulty);
        $this->assertTrue($demo->published_at->lt(now()->subWeek()), 'original publish date kept');

        $solar->refresh();
        $this->assertSame('My own title', $solar->title);
        $this->assertSame('Admin intro', $solar->intro);
        $this->assertSame(6, $solar->questions()->count());
        $this->assertSame('easy', $solar->difficulty, 'empty fields are filled');
        $this->assertStringContainsString('nasa.gov', $solar->sources);
    }

    public function test_friendship_quiz_uses_its_own_result_types(): void
    {
        $this->category();
        $this->seed(QuizLibrarySeeder::class);
        $quiz = Quiz::where('slug', 'how-well-do-you-know-your-best-friend')->with('questions.answers')->first();

        $this->get(route('quizzes.show', $quiz))->assertOk()->assertDontSee('noindex', false)
            ->assertSee('Just for fun, not a scientific test')->assertSee('Last reviewed');

        $pick = fn (int $position) => $quiz->questions->mapWithKeys(fn ($q) => [$q->id => $q->answers->firstWhere('position', $position)->id])->all();

        $res = $this->post(route('quizzes.submit', $quiz), ['answers' => $pick(1)]);
        $this->get($res->headers->get('Location'))->assertSee('Best Friends Forever')->assertSee('years of paying attention')
            ->assertSee('Try another quiz')->assertDontSee('Answers explained');

        $res = $this->post(route('quizzes.submit', $quiz), ['answers' => $pick(2)]); // 2 of 3 points = 0.67
        $this->get($res->headers->get('Location'))->assertSee('Close Friends');

        $res = $this->post(route('quizzes.submit', $quiz), ['answers' => $pick(4)]);
        $this->get($res->headers->get('Location'))->assertSee('Still Getting to Know Each Other')->assertSee('without phones');
    }

    public function test_trivia_pages_show_difficulty_sources_and_score_band(): void
    {
        $this->category();
        $this->seed(QuizLibrarySeeder::class);
        $quiz = Quiz::where('slug', 'cricket-quiz')->with('questions.answers')->first();

        $this->get(route('quizzes.index'))->assertSee('Cricket Quiz')->assertSee('Medium');
        $this->get(route('quizzes.show', $quiz))->assertOk()
            ->assertSee('Facts checked Nov 20, 2026')->assertSee('Sources')
            ->assertSee('<a href="https://www.icc-cricket.com/"', false)
            ->assertSee(route('pages.editorial'), false);

        $right = $quiz->questions->mapWithKeys(fn ($q) => [$q->id => $q->answers->firstWhere('is_correct', true)->id])->all();
        $res = $this->post(route('quizzes.submit', $quiz), ['answers' => $right]);
        $this->get($res->headers->get('Location'))->assertSee('You scored 10/10!')->assertSee('Expert.')
            ->assertSee('Answers explained')->assertSee('Shoaib Akhtar bowled it');

        $this->get('/sitemap.xml')->assertSee(route('quizzes.show', $quiz), false);
    }

    public function test_admin_can_edit_quality_fields(): void
    {
        $cat = $this->category();
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => 'password', 'role' => 'admin']);
        $quiz = Quiz::create(['category_id' => $cat->id, 'slug' => 'x', 'title' => 'X', 'language' => 'en', 'status' => 'draft']);

        $this->actingAs($admin)->get(route('admin.quizzes.edit', $quiz))->assertOk()->assertSee('Facts checked on');

        $this->actingAs($admin)->put(route('admin.quizzes.update', $quiz), [
            'category_id' => $cat->id, 'title' => 'X', 'language' => 'en', 'status' => 'draft',
            'difficulty' => 'hard', 'sources' => "Book one\nhttps://example.org/a", 'reviewed_at' => '2026-11-20',
            'outcomes_json' => json_encode([['min' => 0.5, 'label' => 'High', 'text' => 'T'], ['min' => 0, 'label' => 'Low', 'text' => 'U']]),
            'questions_json' => '[]',
        ])->assertRedirect();

        $quiz->refresh();
        $this->assertSame('hard', $quiz->difficulty);
        $this->assertSame(['Book one', 'https://example.org/a'], $quiz->sourceLines());
        $this->assertSame('2026-11-20', $quiz->reviewed_at->format('Y-m-d'));
        $this->assertSame('High', $quiz->outcomeFor(0.7)['label']);
        $this->assertSame('Low', $quiz->outcomeFor(0.1)['label']);

        $this->actingAs($admin)->put(route('admin.quizzes.update', $quiz), [
            'category_id' => $cat->id, 'title' => 'X', 'language' => 'en', 'status' => 'draft', 'difficulty' => 'impossible',
        ])->assertSessionHasErrors('difficulty');
    }
}
