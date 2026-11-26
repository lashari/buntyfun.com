<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavouritesRecentTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_pages_have_favourite_button_with_clean_path(): void
    {
        $cat = Category::firstOrCreate(['slug' => 'games'], ['department' => 'games', 'name' => 'Games']);
        foreach (['tic-tac-toe', 'chess'] as $slug) {
            Game::create(['category_id' => $cat->id, 'title' => ucwords(str_replace('-', ' ', $slug)), 'slug' => $slug, 'status' => 'published']);
        }
        $this->get('/games/tic-tac-toe')->assertOk()
            ->assertSee('Add to favourites')
            ->assertSee('BF.visited(item)', false)
            ->assertSee('tic-tac-toe\u0022,\u0022title\u0022:\u0022Tic Tac Toe', false);
        $this->get('/games')->assertOk()->assertSee('Your favourites')->assertSee('Recently played');
    }

    public function test_home_and_quizzes_show_the_saved_block(): void
    {
        $this->get('/')->assertOk()->assertSee('Your games and quizzes');
        $this->get('/quizzes')->assertOk()->assertSee('Saved only in this browser');
    }
}
