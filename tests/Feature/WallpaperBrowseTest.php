<?php

namespace Tests\Feature;

use App\Models\Wallpaper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WallpaperBrowseTest extends TestCase
{
    use RefreshDatabase;

    private function make(string $category, int $n, string $status = 'published'): void
    {
        for ($i = 1; $i <= $n; $i++) {
            Wallpaper::create([
                'title' => "{$category} {$i}", 'slug' => "{$category}-{$i}-x", 'category' => $category,
                'orientation' => 'desktop', 'width' => 1920, 'height' => 1080, 'file_path' => "wallpapers/{$category}-{$i}.jpg",
                'license' => 'public_domain', 'status' => $status,
            ]);
        }
    }

    public function test_index_groups_by_category_with_counts(): void
    {
        $this->make('cars', 8);
        $this->make('space', 2);
        $this->make('trains', 3, 'in_review');

        $this->get(route('wallpapers.index'))->assertOk()
            ->assertSee('Cars <span class="opacity-70">(8)</span>', false)
            ->assertSee(route('wallpapers.category', 'space'))
            ->assertSee('cars 8')->assertDontSee('cars 2<')   // 6 newest per section
            ->assertDontSee('Trains');
        $this->get('/wallpapers?category=cars')->assertRedirect(route('wallpapers.category', 'cars'))->assertStatus(301);
    }

    public function test_category_page_has_back_and_next(): void
    {
        $this->make('cars', 30);
        $this->get(route('wallpapers.category', 'cars'))->assertOk()
            ->assertSee('Page 1 of 2')->assertSee('rel="next"', false)->assertDontSee('rel="prev"', false);
        $this->get(route('wallpapers.category', 'cars').'?page=2')->assertOk()
            ->assertSee('Page 2 of 2')->assertSee('rel="prev"', false)->assertSee('noindex', false);
        $this->get(route('wallpapers.category', 'nothing'))->assertNotFound();
    }

    public function test_show_page_back_next_stay_in_category(): void
    {
        $this->make('cars', 3);
        $this->make('space', 1);
        $middle = Wallpaper::where('slug', 'cars-2-x')->first();

        $this->get(route('wallpapers.show', $middle))->assertOk()
            ->assertSee(route('wallpapers.show', 'cars-3-x'))   // back = newer
            ->assertSee(route('wallpapers.show', 'cars-1-x'))   // next = older
            ->assertSee('2 of 3 in Cars')->assertSee('top-1/2', false)
            ->assertDontSee(route('wallpapers.show', 'space-1-x'));
    }
}
