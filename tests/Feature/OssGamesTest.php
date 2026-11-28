<?php

namespace Tests\Feature;

use App\Content\OssGames;
use App\Models\Category;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OssGamesTest extends TestCase
{
    use RefreshDatabase;

    private function game(string $slug, string $status, ?array $config = null): Game
    {
        $cat = Category::firstOrCreate(['slug' => 'games'], ['department' => 'games', 'name' => 'Games']);

        return Game::create(['category_id' => $cat->id, 'slug' => $slug, 'title' => OssGames::for($slug)['title'] ?? $slug, 'status' => $status, 'config_json' => $config]);
    }

    public function test_every_catalog_game_ships_its_files_and_licence(): void
    {
        foreach (OssGames::ALL as $slug => $g) {
            $dir = public_path('play/'.$slug);
            $this->assertFileExists($dir.'/'.$g['entry'], $slug);
            $this->assertNotEmpty(glob($dir.'/{LICENSE,LICENSE.*,license.*}', GLOB_BRACE), "$slug has no licence file");
            $this->assertNotNull(OssGames::thumb($slug), "$slug has no card picture");
            $this->assertContains($g['license'], ['MIT', 'BSD-2-Clause', 'BSD-3-Clause'], $slug);
            foreach ($g['audience'] as $a) {
                $this->assertArrayHasKey($a, OssGames::AUDIENCES, $slug);
            }
        }
    }

    public function test_in_review_game_is_hidden_from_public_but_admin_can_preview(): void
    {
        $game = $this->game('2048', 'in_review');

        $this->get(route('games.show', $game))->assertNotFound();
        $this->get(route('games.index'))->assertDontSee('/games/2048', false);

        $admin = User::create(['name' => 'A', 'email' => 'a@test.com', 'password' => 'password', 'role' => 'admin']);
        $this->actingAs($admin)->get(route('games.show', $game))
            ->assertOk()
            ->assertSee('/play/2048/index.html', false)
            ->assertSee('Preview')
            ->assertSee('noindex', false);
    }

    public function test_published_game_shows_credit_and_age_filter_works(): void
    {
        $this->game('tower-builder', 'published');
        $this->game('the-house', 'published');

        $this->get(route('games.index'))->assertSee('/images/games/tower-builder.svg', false);

        $this->get(route('games.show', 'tower-builder'))->assertOk()
            ->assertSee('github.com/iamkun/tower_game', false)
            ->assertSee('MIT');

        // Card headings only: the header menu now lists popular games on every page.
        $this->get(route('games.index', ['for' => 'kids']))->assertOk()
            ->assertSee('<h2 class="font-semibold">Tower Builder</h2>', false)->assertDontSee('<h2 class="font-semibold">The House</h2>', false);
        $this->get(route('games.index', ['for' => 'adults']))->assertOk()
            ->assertSee('<h2 class="font-semibold">The House</h2>', false)->assertDontSee('<h2 class="font-semibold">Tower Builder</h2>', false);
        $this->get(route('games.index', ['for' => 'nonsense']))->assertOk()
            ->assertSee('Tower Builder')->assertSee('The House');
    }

    public function test_admin_audience_choice_overrides_catalog(): void
    {
        $game = $this->game('the-house', 'published', ['audience' => ['kids']]);
        $this->assertSame(['kids'], OssGames::audienceOf($game));

        $admin = User::create(['name' => 'A', 'email' => 'b@test.com', 'password' => 'password', 'role' => 'admin']);
        $this->actingAs($admin)->put(route('admin.games.update', $game), [
            'category_id' => $game->category_id, 'title' => 'The House', 'slug' => 'the-house',
            'status' => 'published', 'audience' => ['adults', 'teens'],
        ])->assertRedirect();

        $this->assertSame(['adults', 'teens'], $game->fresh()->config_json['audience']);
    }

    public function test_original_game_shows_buntyfun_credit_not_a_source_link(): void
    {
        $this->game('stunt-hopper', 'published');

        $this->get(route('games.show', 'stunt-hopper'))->assertOk()
            ->assertSee('/play/stunt-hopper/index.html', false)
            ->assertSee('A BuntyFun original game')
            ->assertDontSee('Source code');
    }

    public function test_landscape_games_get_a_taller_box_on_phones(): void
    {
        $this->game('skyline-flight', 'published');

        $this->get(route('games.show', 'skyline-flight'))->assertOk()
            ->assertSee('aspect-ratio: 16/9; --ratio-mobile: 4/5;', false)
            ->assertSee('.game-box { aspect-ratio: var(--ratio-mobile) !important; }', false);
    }
}
