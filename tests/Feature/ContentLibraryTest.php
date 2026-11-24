<?php

namespace Tests\Feature;

use App\Content\Countries;
use App\Content\GameGuides;
use App\Content\TriviaStarter;
use App\Content\WordLists;
use App\Models\Category;
use App\Models\DailyFeature;
use App\Models\Game;
use App\Models\Quiz;
use App\Models\Quote;
use App\Models\TriviaQuestion;
use App\Models\User;
use App\Services\Library\DailyPicker;
use App\Services\Library\QuizBuilder;
use App\Services\Library\QuoteImporter;
use App\Services\Library\TriviaImporter;
use App\Services\Library\TriviaStarterLoader;
use App\Services\Library\TriviaValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContentLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Category::firstOrCreate(['slug' => 'quizzes'], ['department' => 'quizzes', 'name' => 'Quizzes']);
        Category::firstOrCreate(['slug' => 'games'], ['department' => 'games', 'name' => 'Games']);
    }

    private function admin(): User
    {
        return User::create(['name' => 'A', 'email' => 'a'.uniqid().'@test.com', 'password' => 'password', 'role' => 'admin']);
    }

    private function publishStarter(): void
    {
        app(TriviaStarterLoader::class)->load();
        Quiz::query()->update(['status' => 'published', 'published_at' => now()]);
    }

    // ---- Starter pack -------------------------------------------------------------------

    public function test_starter_pack_is_100_complete_questions_with_explanations(): void
    {
        $this->assertCount(10, TriviaStarter::QUIZZES);
        $prompts = [];
        foreach (TriviaStarter::QUIZZES as $quiz) {
            $this->assertCount(10, $quiz['questions'], $quiz['slug']);
            $this->assertArrayHasKey($quiz['topic'], \App\Content\QuizTopics::ALL);
            foreach ($quiz['questions'] as [$prompt, $correct, $wrong, $why, $level]) {
                [$reject] = (new TriviaValidator)->check($prompt, $correct, $wrong);
                $this->assertNull($reject, "$prompt: $reject");
                $this->assertGreaterThan(40, mb_strlen($why), $prompt);
                $this->assertContains($level, ['easy', 'medium', 'hard']);
                $prompts[] = $prompt;
            }
        }
        $this->assertCount(100, array_unique($prompts));
    }

    public function test_loader_creates_ten_quizzes_in_review_and_is_idempotent(): void
    {
        $s = app(TriviaStarterLoader::class)->load();
        $this->assertSame(10, $s['quizzes']);
        $this->assertSame(100, TriviaQuestion::where('status', 'approved')->whereNotNull('quiz_id')->count());
        $this->assertSame(10, Quiz::where('status', 'in_review')->count());
        $quiz = Quiz::where('slug', 'maths-quiz')->first();
        $this->assertSame('maths', $quiz->topic);
        $this->assertStringContainsString('CC BY-SA 4.0', $quiz->sources);
        $this->assertSame(10, $quiz->questions()->count());
        $this->assertSame(10, $quiz->questions->filter(fn ($q) => $q->answers()->where('is_correct', true)->count() === 1)->count());

        $again = app(TriviaStarterLoader::class)->load();
        $this->assertSame(0, $again['quizzes']);
        $this->assertSame(10, Quiz::count());
    }

    public function test_in_review_quiz_is_hidden_but_moderator_can_preview(): void
    {
        app(TriviaStarterLoader::class)->load();
        $quiz = Quiz::where('slug', 'animal-quiz')->first();
        $this->get(route('quizzes.show', $quiz))->assertNotFound();
        $this->actingAs($this->admin())->get(route('quizzes.show', $quiz))->assertOk()->assertSee('Preview')->assertSee('noindex', false);
    }

    // ---- Importers ------------------------------------------------------------------------

    public function test_validator_rejects_broken_and_unsuitable_questions_and_flags_sensitive_ones(): void
    {
        $v = new TriviaValidator;
        $this->assertNotNull($v->check('Which of these is true about it?', 'All of these', ['A', 'B', 'C'])[0]);
        $this->assertNotNull($v->check('What does x equal in this equation?', '2', ['4', '6', '8'])[0]);
        $this->assertNotNull($v->check('Which beer is brewed in Munich?', 'Paulaner', ['A', 'B', 'C'])[0]);
        $this->assertNotNull($v->check('Pick the same answer twice please', 'One', ['One', 'Two', 'Three'])[0]);
        [$reject, $flags] = $v->check('What is the capital of Israel according to it?', 'Jerusalem', ['Tel Aviv', 'Haifa', 'Eilat']);
        $this->assertNull($reject);
        $this->assertNotEmpty($flags);
        $this->assertSame('Tom & Jerry’s "show"', TriviaValidator::clean('Tom &amp; Jerry’s &quot;show&quot; '));
    }

    public function test_opentdb_import_decodes_dedupes_and_saves_in_review(): void
    {
        $q = fn ($text, $ans) => ['type' => 'multiple', 'difficulty' => 'easy', 'question' => rawurlencode($text), 'correct_answer' => rawurlencode($ans),
            'incorrect_answers' => array_map('rawurlencode', ['Paris', 'Rome', 'Madrid'])];
        Http::fake([
            'opentdb.com/api_token.php*' => Http::response(['token' => 'tok']),
            'opentdb.com/api.php*' => Http::response(['response_code' => 0, 'results' => [
                $q('What is the capital of Germany & its largest city?', 'Berlin'),
                $q('What is the capital of Germany & its largest city?', 'Berlin'),
                $q('Which wine region is famous?', 'Bordeaux'),
            ]]),
        ]);
        $s = app(TriviaImporter::class)->fromOpenTdb('geography', 3);
        $this->assertSame([1, 1, 1], [$s['saved'], $s['duplicates'], $s['rejected']]);
        $row = TriviaQuestion::first();
        $this->assertSame('in_review', $row->status);
        $this->assertSame('What is the capital of Germany & its largest city?', $row->prompt);
        $this->assertSame(['Paris', 'Rome', 'Madrid'], $row->wrong_answers);
    }

    public function test_opentriviaqa_parser_keeps_only_complete_questions(): void
    {
        $text = "#Q Which is the smallest ocean?\n^ The Arctic Ocean\nA The Atlantic Ocean\nB The Arctic Ocean\nC The Indian Ocean\nD The Southern Ocean\n\n"
            ."#Q Broken one\n^ Missing\nA One\nB Two\n\n#Q Answer not among options\n^ Z\nA A\nB B\nC C\nD D\n";
        $rows = TriviaImporter::parseOpenTriviaQa($text);
        $this->assertCount(1, $rows);
        $this->assertSame('The Arctic Ocean', $rows[0]['correct']);
        $this->assertCount(3, $rows[0]['wrong']);
    }

    public function test_quiz_builder_needs_ten_approved_explained_questions(): void
    {
        $importer = app(TriviaImporter::class);
        $rows = [];
        for ($i = 1; $i <= 12; $i++) {
            $rows[] = ['prompt' => "Test animal question number {$i}?", 'correct' => "Right {$i}", 'wrong' => ["W{$i}a", "W{$i}b", "W{$i}c"]];
        }
        $importer->save('opentdb', 'animals', $rows);
        $builder = app(QuizBuilder::class);
        TriviaQuestion::query()->update(['status' => 'approved']);
        $this->assertNull($builder->buildOne('animals'), 'no explanations yet');

        TriviaQuestion::query()->update(['explanation' => 'Because this is the right answer for the test.']);
        $quiz = $builder->buildOne('animals');
        $this->assertNotNull($quiz);
        $this->assertSame('in_review', $quiz->status);
        $this->assertSame(10, $quiz->questions()->count());
        $this->assertSame(2, $builder->available('animals')->count());
        $this->assertNull($builder->buildOne('animals'));
    }

    public function test_quote_import_maps_topics_flags_disputed_authors_and_dedupes(): void
    {
        $s = app(QuoteImporter::class)->save([
            ['id' => 1, 'quote' => 'Action is eloquence.', 'author' => 'William Shakespeare', 'tags' => ['motivation']],
            ['id' => 2, 'quote' => 'Action is eloquence!', 'author' => 'Someone', 'tags' => ['wisdom']],
            ['id' => 3, 'quote' => 'Peace comes from within. Do not seek it without.', 'author' => 'Buddha', 'tags' => ['empathy']],
            ['id' => 4, 'quote' => 'A glass of wine a day keeps me calm.', 'author' => 'X', 'tags' => ['life']],
            ['id' => 5, 'quote' => 'Be the change that you wish to see.', 'author' => 'Unknown', 'tags' => ['change']],
        ]);
        $this->assertSame([3, 1, 1], [$s['saved'], $s['duplicates'], $s['rejected']]);
        $this->assertSame('kindness', Quote::where('author', 'Buddha')->value('topic'));
        $this->assertNotEmpty(Quote::where('author', 'Buddha')->first()->flags);
        $this->assertSame('Anonymous', Quote::where('source_ref', 'quoteslate:5')->value('author'));
        $this->assertSame(0, Quote::where('status', 'published')->count());
    }

    // ---- Public pages -----------------------------------------------------------------------

    public function test_quiz_topic_pages_and_index_filters(): void
    {
        $this->publishStarter();
        $this->get(route('quizzes.index'))->assertOk()->assertSee('Geography')->assertSee(route('quizzes.topic', 'geography'), false);
        $this->get(route('quizzes.topic', 'geography'))->assertOk()->assertSee('Oceans, Rivers and Mountains Quiz')->assertDontSee('noindex', false);
        $this->get(route('quizzes.topic', 'maths'))->assertOk()->assertSee('noindex', false); // only one quiz yet
        $this->get(route('quizzes.topic', 'nope'))->assertNotFound();
        $this->get(route('quizzes.index', ['level' => 'easy']))->assertOk();
    }

    public function test_quiz_result_shows_score_grid_and_explanations(): void
    {
        $this->publishStarter();
        $quiz = Quiz::where('slug', 'maths-quiz')->with('questions.answers')->first();
        $answers = $quiz->questions->mapWithKeys(fn ($q) => [$q->id => $q->answers->firstWhere('is_correct', true)->id])->all();
        $res = $this->post(route('quizzes.submit', $quiz), ['answers' => $answers]);
        $this->followRedirects($res)->assertOk()->assertSee('10/10')->assertSee('🟩🟩🟩', false)->assertSee('Hardy–Ramanujan');
    }

    public function test_daily_page_quotes_pages_and_daily_trivia(): void
    {
        $this->publishStarter();
        app(QuoteImporter::class)->save(collect(range(1, 12))->map(fn ($i) => ['id' => $i, 'quote' => "Work hard and be kind, lesson number {$i}.", 'author' => 'Test Author', 'tags' => ['success']])->all());
        Quote::query()->update(['status' => 'published']);

        $this->get(route('daily'))->assertOk()->assertSee('Daily Trivia')->assertSee('Quiz of the day')->assertSee('Quote of the day');
        $this->assertCount(5, app(DailyPicker::class)->trivia());
        $this->assertEquals(app(DailyPicker::class)->trivia()->pluck('id'), app(DailyPicker::class)->trivia()->pluck('id'));

        $this->get(route('quotes.index'))->assertOk()->assertSee('Quote of the day')->assertSee('Success');
        $this->get(route('quotes.index', ['q' => 'lesson number 3']))->assertOk()->assertSee('lesson number 3.')->assertSee('noindex', false);
        $this->get(route('quotes.topic', 'success'))->assertOk()->assertDontSee('<meta name="robots" content="noindex', false);
        $this->get(route('quotes.author', 'test-author'))->assertOk()->assertSee('Test Author Quotes');
        $this->get(route('quotes.show', Quote::first()))->assertOk()->assertSee('noindex', false);
        $this->get(route('quotes.topic', 'love'))->assertNotFound();

        $xml = $this->get(route('sitemap'))->assertOk()->getContent();
        foreach ([route('daily'), route('quotes.index'), route('quotes.topic', 'success'), route('quotes.author', 'test-author'), route('quizzes.topic', 'geography')] as $url) {
            $this->assertStringContainsString($url, $xml);
        }
        $this->assertStringNotContainsString(route('quizzes.topic', 'maths'), $xml);
    }

    public function test_daily_schedule_pin_and_auto_fill(): void
    {
        $this->publishStarter();
        $picker = app(DailyPicker::class);
        $this->assertSame(7, $picker->fillAhead(7)); // only quizzes are published here
        $this->assertSame(0, $picker->fillAhead(7));

        $quiz = Quiz::where('slug', 'sports-quiz')->first();
        $this->actingAs($this->admin())->post(route('admin.daily.update'), ['date' => today()->toDateString(), 'type' => 'quiz', 'item_id' => $quiz->id])->assertRedirect();
        $this->assertTrue(DailyFeature::whereDate('feature_date', today())->where('type', 'quiz')->value('pinned'));
        $this->assertSame($quiz->id, $picker->item('quiz')->id);
        $this->get(route('admin.daily.index'))->assertOk()->assertSee('📌');
    }

    public function test_every_new_game_page_renders_with_guide(): void
    {
        $cat = Category::where('slug', 'games')->first();
        $this->publishStarter();
        foreach (GameGuides::NEW_GAMES as $slug => [$title]) {
            $game = Game::create(['category_id' => $cat->id, 'slug' => $slug, 'title' => $title, 'status' => 'published']);
            $this->get(route('games.show', $game))->assertOk()->assertSee($title)->assertSee('How to play')->assertSee('x-data', false);
            $this->assertArrayHasKey('summary', GameGuides::for($slug));
        }
    }

    public function test_game_data_is_complete(): void
    {
        foreach (Countries::ALL as $code => [$name]) {
            $this->assertFileExists(public_path("images/flags/{$code}.svg"), $name);
        }
        $this->assertGreaterThan(120, count(Countries::forJs(true)));
        foreach (WordLists::WORD_SEARCH as $theme => $words) {
            $this->assertGreaterThanOrEqual(10, count($words), $theme);
            foreach ($words as $w) {
                $this->assertMatchesRegularExpression('/^[A-Z]{3,9}$/', $w, "$theme: $w");
            }
        }
        foreach (WordLists::HANGMAN as $cat => $words) {
            foreach ($words as [$w, $hint]) {
                $this->assertMatchesRegularExpression('/^[A-Z]{4,12}$/', $w, "$cat: $w");
                $this->assertNotEmpty($hint);
            }
        }
    }

    // ---- Admin ------------------------------------------------------------------------------

    public function test_question_bank_admin_flow(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.question-bank.starter'))->assertRedirect();
        $this->assertSame(10, Quiz::count());

        app(TriviaImporter::class)->save('opentdb', 'science', [['prompt' => 'Which planet is known as the Red Planet?', 'correct' => 'Mars', 'wrong' => ['Venus', 'Jupiter', 'Saturn']]]);
        $q = TriviaQuestion::where('status', 'in_review')->first();

        $this->get(route('admin.question-bank.index'))->assertOk()->assertSee('Red Planet')->assertSee('No explanation yet');
        $this->post(route('admin.question-bank.bulk'), ['ids' => [$q->id], 'action' => 'approve'])->assertRedirect();
        $this->assertSame('in_review', $q->fresh()->status, 'cannot approve without explanation');

        $this->put(route('admin.question-bank.update', $q), [
            'topic' => 'science', 'difficulty' => 'easy', 'prompt' => $q->prompt, 'correct_answer' => 'Mars', 'wrong' => ['Venus', 'Jupiter', 'Saturn'],
            'explanation' => 'Iron oxide (rust) in its soil and dust makes Mars look red.', 'status' => 'approved',
        ])->assertRedirect();
        $this->assertSame('approved', $q->fresh()->status);

        $q->update(['status' => 'in_review']);
        $editor = User::create(['name' => 'E', 'email' => 'qe@test.com', 'password' => 'password', 'role' => 'editor']);
        $this->actingAs($editor)->put(route('admin.question-bank.update', $q), [
            'topic' => 'science', 'difficulty' => 'easy', 'prompt' => $q->prompt, 'correct_answer' => 'Mars', 'wrong' => ['Venus', 'Jupiter', 'Saturn'],
            'explanation' => 'Iron oxide (rust) in its soil and dust makes Mars look red.', 'status' => 'approved',
        ])->assertRedirect();
        $this->assertSame('in_review', $q->fresh()->status, 'editor cannot approve via the edit form');
        $this->actingAs($editor)->post(route('admin.question-bank.bulk'), ['ids' => [$q->id], 'action' => 'approve'])->assertForbidden();
        $this->actingAs($editor)->post(route('admin.question-bank.starter'))->assertForbidden();
        $this->actingAs($admin);
        $this->get(route('admin.question-bank.edit', $q))->assertOk();
        $this->get(route('admin.quizzes.index', ['status' => 'in_review']))->assertOk()->assertSee('Preview');
    }

    public function test_quotes_admin_publish_and_editor_limits(): void
    {
        app(QuoteImporter::class)->save([['id' => 1, 'quote' => 'Knowledge is power, use it well.', 'author' => 'Test', 'tags' => ['knowledge']]]);
        $quote = Quote::first();
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.quotes.index'))->assertOk()->assertSee('Knowledge is power');
        $this->post(route('admin.quotes.bulk'), ['ids' => [$quote->id], 'action' => 'publish'])->assertRedirect();
        $this->assertSame('published', $quote->fresh()->status);

        $editor = User::create(['name' => 'E', 'email' => 'e@test.com', 'password' => 'password', 'role' => 'editor']);
        $this->actingAs($editor)->post(route('admin.quotes.bulk'), ['ids' => [$quote->id], 'action' => 'publish'])->assertForbidden();
        $this->actingAs($admin)->get(route('admin.quotes.edit', $quote))->assertOk();
    }

    public function test_migration_registered_automation_jobs(): void
    {
        foreach (['import_trivia', 'build_quizzes', 'import_quotes', 'daily_picks', 'import_wallpapers'] as $key) {
            $this->assertDatabaseHas('automation_jobs', ['key' => $key]);
            $this->assertArrayHasKey($key, \App\Services\Automation\AutomationRunner::TASKS);
        }
        $this->assertFalse((bool) \App\Models\AutomationJob::where('key', 'import_wallpapers')->value('enabled'));
    }

    public function test_search_covers_quotes_messages_and_quiz_intros_and_header_has_search_toggle(): void
    {
        $this->publishStarter();
        app(QuoteImporter::class)->save([['id' => 1, 'quote' => 'Imagination is more important than knowledge.', 'author' => 'Albert Einstein', 'tags' => ['knowledge']]]);
        Quote::query()->update(['status' => 'published']);
        \App\Models\Message::create(['category' => 'good_morning', 'language' => 'en', 'title' => 'Sunny mango morning', 'body' => 'Good morning! Have a sweet day.', 'slug' => 'sunny-mango', 'status' => 'published']);

        $this->get(route('search', ['q' => 'einstein']))->assertOk()->assertSee('Imagination is more important')->assertSee('Quotes');
        $this->get(route('search', ['q' => 'mango']))->assertOk()->assertSee('Sunny mango morning');
        $this->get(route('search', ['q' => 'Kyrgyzstan']))->assertOk()->assertSee('No results');
        $this->get(route('search', ['q' => 'time zones']))->assertOk()->assertSee('General Knowledge Quiz');   // matched in the quiz intro
        $this->get(route('search', ['q' => '100%']))->assertOk();                                             // LIKE wildcard escaped
        $this->get(route('search'))->assertOk()->assertSee('Search BuntyFun')->assertSee('search-page-q', false);

        $home = $this->get(route('home'))->assertOk();
        $home->assertSee('site-search-bar', false)->assertSee('aria-label="Search BuntyFun"', false)->assertSee('whitespace-nowrap', false);
    }
}
