<?php

namespace Tests\Feature;

use App\Models\BirthdayMessage;
use App\Models\Category;
use App\Models\Game;
use App\Models\NameEntry;
use App\Models\Quiz;
use Database\Seeders\QuizLibrarySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdsenseReadinessTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $slug): Category
    {
        return Category::firstOrCreate(['slug' => $slug], ['department' => $slug, 'name' => ucfirst($slug)]);
    }

    private function wishes(string $relationship, int $n): void
    {
        $cat = $this->category('birthday');
        foreach (range(1, $n) as $i) {
            BirthdayMessage::create(['category_id' => $cat->id, 'relationship' => $relationship, 'tone' => $i % 2 ? 'funny' : 'short',
                'language' => 'en', 'body' => "Wish {$relationship} number {$i}, happy birthday!", 'slug' => "{$relationship}-{$i}", 'status' => 'published']);
        }
    }

    public function test_trust_pages_load_and_are_linked_in_the_footer(): void
    {
        foreach (['/about', '/contact', '/privacy-policy', '/terms', '/editorial-policy'] as $path) {
            $this->get($path)->assertOk()->assertDontSee('noindex', false);
        }
        $this->get('/privacy')->assertRedirect('/privacy-policy');
        $this->get('/')->assertSee(route('pages.privacy'), false)->assertSee(route('pages.contact'), false)->assertSee(route('pages.terms'), false);
    }

    public function test_privacy_policy_has_required_adsense_disclosures(): void
    {
        $this->get('/privacy-policy')
            ->assertSee('Third-party vendors, including Google, use cookies')
            ->assertSee('adssettings.google.com', false)
            ->assertSee('policies.google.com/technologies/partner-sites', false)
            ->assertSee('aboutads.info', false);
    }

    public function test_contact_shows_configured_email_only(): void
    {
        $this->get('/contact')->assertOk()->assertDontSee('mailto:', false);
        Config::set('site.contact_email', 'hello@example.test');
        $this->get('/contact')->assertSee('mailto:hello@example.test', false);
    }

    public function test_ads_txt_is_built_from_real_client_id_only(): void
    {
        $this->get('/ads.txt')->assertNotFound();
        Config::set('services.adsense.client_id', 'not-a-real-id');
        $this->get('/ads.txt')->assertNotFound();
        Config::set('services.adsense.client_id', 'ca-pub-1234567890123456');
        $this->get('/ads.txt')->assertOk()->assertSee('google.com, pub-1234567890123456, DIRECT, f08c47fec0942fa0', false);
    }

    public function test_consent_mode_default_comes_before_google_scripts(): void
    {
        Config::set('services.ga4.measurement_id', 'G-TEST');
        Config::set('services.adsense.client_id', 'ca-pub-1234567890123456');
        $html = $this->get('/games')->assertOk()->getContent();
        $consent = strpos($html, "gtag('consent', 'default'");
        $this->assertNotFalse($consent);
        $this->assertLessThan(strpos($html, 'googletagmanager.com/gtag/js'), $consent);
        $this->assertLessThan(strpos($html, 'adsbygoogle.js'), $consent);
    }

    public function test_no_ad_unit_without_a_real_slot_id_and_no_ads_on_noindex_pages(): void
    {
        Config::set('services.adsense.client_id', 'ca-pub-1234567890123456');
        $this->get('/games')->assertDontSee('<ins class="adsbygoogle"', false)->assertDontSee('data-ad-slot="auto"', false);
        Config::set('services.adsense.slot_id', '1234567890');
        $this->get('/games')->assertSee('data-ad-slot="1234567890"', false);
        // policy pages, search and 404s never load the ad script
        $this->get('/privacy-policy')->assertDontSee('adsbygoogle', false);
        $this->get('/search?q=x')->assertDontSee('adsbygoogle', false)->assertSee('noindex', false);
        $this->get('/no-such-page')->assertNotFound()->assertDontSee('adsbygoogle', false);
    }

    public function test_birthday_hub_is_indexable_and_single_wishes_are_noindex(): void
    {
        $this->wishes('mother', 4);
        $this->wishes('colleague', 2); // under 3 → no hub

        $this->get('/birthday-wishes/for/mother')->assertOk()
            ->assertSee('Birthday Wishes for Mom')->assertSee('How to make it personal')
            ->assertSee('Wish mother number 3')->assertDontSee('noindex', false);
        $this->get('/birthday-wishes/for/colleague')->assertNotFound();
        $this->get('/birthday-wishes/for/unknown')->assertNotFound();

        $this->get('/birthday-wishes/mother-1')->assertOk()->assertSee('noindex, follow', false)
            ->assertSee(route('birthday-messages.relationship', 'mother'), false);
        $this->get('/birthday-wishes?tone=funny')->assertSee('noindex', false);

        $sitemap = $this->get('/sitemap.xml')->assertOk();
        $sitemap->assertSee(route('birthday-messages.relationship', 'mother'), false)
            ->assertDontSee('/birthday-wishes/mother-1', false)
            ->assertSee(route('pages.privacy'), false)
            ->assertDontSee(route('wallpapers.index'), false); // empty section
    }

    public function test_multiword_relationship_slug(): void
    {
        $this->wishes('best_friend', 3);
        $this->get('/birthday-wishes/for/best-friend')->assertOk()->assertSee('Birthday Wishes for Your Best Friend');
    }

    public function test_quiz_library_seeds_once_and_results_explain_answers(): void
    {
        $this->category('quizzes');
        $this->seed(QuizLibrarySeeder::class);
        $this->seed(QuizLibrarySeeder::class);
        $this->assertSame(3, Quiz::count());

        $quiz = Quiz::where('slug', 'solar-system-quiz')->with('questions.answers')->first();
        $this->assertCount(10, $quiz->questions);
        $this->get(route('quizzes.show', $quiz))->assertOk()->assertSee($quiz->intro)->assertDontSee('noindex', false);

        // answer everything with the first option
        $answers = $quiz->questions->mapWithKeys(fn ($q) => [$q->id => $q->answers->first()->id])->all();
        $res = $this->post(route('quizzes.submit', $quiz), ['answers' => $answers]);
        $res->assertRedirect();
        $this->get($res->headers->get('Location'))->assertOk()
            ->assertSee('Answers explained')->assertSee('Correct answer: <span class="font-medium text-emerald-700">Mercury</span>', false)
            ->assertSee('Pluto was reclassified')->assertSee('noindex', false)->assertDontSee('adsbygoogle', false);

        $this->get('/sitemap.xml')->assertSee(route('quizzes.show', $quiz), false);
    }

    public function test_short_quiz_is_noindex_and_left_out_of_sitemap(): void
    {
        $quiz = Quiz::create(['category_id' => $this->category('quizzes')->id, 'slug' => 'tiny', 'title' => 'Tiny', 'language' => 'en', 'status' => 'published']);
        $q = $quiz->questions()->create(['position' => 1, 'prompt' => 'Q?']);
        $q->answers()->create(['position' => 1, 'label' => 'A', 'score_value' => 1]);

        $this->get(route('quizzes.show', $quiz))->assertSee('noindex', false);
        $this->get('/sitemap.xml')->assertDontSee(route('quizzes.show', $quiz), false);
    }

    public function test_game_pages_have_rules_and_scoring(): void
    {
        $cat = $this->category('games');
        foreach (['tic-tac-toe' => 'Tic-Tac-Toe', 'memory-match' => 'Memory Match', 'word-scramble' => 'Word Scramble',
            'rock-paper-scissors' => 'Rock Paper Scissors', 'daily-brain-challenge' => 'Daily Brain Challenge'] as $slug => $title) {
            Game::create(['category_id' => $cat->id, 'slug' => $slug, 'title' => $title, 'status' => 'published']);
        }
        foreach (Game::all() as $game) {
            $this->get(route('games.show', $game))->assertOk()
                ->assertSee('How to play '.$game->title)->assertSee('Scoring')->assertSee('Tips');
        }
        $this->get('/games')->assertSee('three-in-a-row');
    }

    public function test_name_page_shows_spellings_and_linked_source(): void
    {
        NameEntry::create(['name_en' => 'Ahmad', 'name_ar' => 'أحمد', 'name_ur' => 'احمد', 'gender' => 'male', 'meaning' => 'Most praised.',
            'origin' => 'Arabic', 'source_ref' => 'Wikipedia, "Ahmad": https://en.wikipedia.org/wiki/Ahmad', 'slug' => 'ahmad', 'status' => 'published']);
        $this->get('/islamic-names/ahmad')->assertOk()
            ->assertSee('احمد')->assertSee('href="https://en.wikipedia.org/wiki/Ahmad"', false)->assertSee('Boy');
    }

    public function test_mobile_menu_exists(): void
    {
        $this->get('/')->assertSee('id="mobile-menu"', false)->assertSee('aria-controls="mobile-menu"', false);
    }
}
