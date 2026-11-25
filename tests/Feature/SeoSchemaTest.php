<?php

namespace Tests\Feature;

use App\Models\Wallpaper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** SEO 10/10 pass (11-25-2026): page schema, 404 canonical, mobile menu search. */
class SeoSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function ld(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

        return array_map(fn ($j) => json_decode($j, true, flags: JSON_THROW_ON_ERROR), $m[1]);
    }

    public function test_wallpaper_page_has_image_object_with_licence(): void
    {
        $w = Wallpaper::create([
            'title' => 'Moon', 'slug' => 'moon-x', 'category' => 'space', 'orientation' => 'desktop', 'width' => 1920, 'height' => 1080,
            'file_path' => 'wallpapers/moon.jpg', 'license' => 'public_domain', 'status' => 'published',
            'attribution' => 'NASA', 'source_url' => 'https://images.nasa.gov/details/x',
        ]);
        $types = collect($this->ld($this->get(route('wallpapers.show', $w))->assertOk()->getContent()))->keyBy('@type');
        $img = $types['ImageObject'];
        $this->assertSame('https://creativecommons.org/publicdomain/mark/1.0/', $img['license']);
        $this->assertSame('NASA', $img['creditText']);
        $this->assertSame('https://images.nasa.gov/details/x', $img['acquireLicensePage']);
        $this->assertArrayNotHasKey('aggregateRating', $img);
        $this->assertTrue($types->has('BreadcrumbList'));

        // unknown licence version -> no licence URL is made up
        $w->update(['license' => 'cc_by']);
        $img = collect($this->ld($this->get(route('wallpapers.show', $w))->getContent()))->firstWhere('@type', 'ImageObject');
        $this->assertArrayNotHasKey('license', $img);
    }

    public function test_category_page_has_item_list_and_each_schema_once(): void
    {
        Wallpaper::create(['title' => 'Car', 'slug' => 'car-x', 'category' => 'cars', 'orientation' => 'desktop', 'width' => 1, 'height' => 1,
            'file_path' => 'w/c.jpg', 'license' => 'cc0', 'status' => 'published']);
        $blocks = $this->ld($this->get(route('wallpapers.category', 'cars'))->getContent());
        $types = array_column($blocks, '@type');
        $this->assertSame(array_unique($types), $types, 'no duplicate schema types');
        $list = collect($blocks)->firstWhere('@type', 'ItemList');
        $this->assertSame(route('wallpapers.show', 'car-x'), $list['itemListElement'][0]['url']);
    }

    public function test_404_has_no_canonical_and_is_noindex(): void
    {
        $this->get('/no-such-page-xyz')->assertNotFound()
            ->assertDontSee('rel="canonical"', false)
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_mobile_menu_has_search_and_focus_hooks(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('id="menu-search"', false)
            ->assertSee('x-ref="menuBtn"', false)
            ->assertSee('@keydown.escape.window="esc()"', false)
            ->assertSee('aria-controls="mobile-menu"', false);
    }

    public function test_menu_section_opens_on_pages_under_its_hub(): void
    {
        Wallpaper::create(['title' => 'Jet', 'slug' => 'jet-x', 'category' => 'airplanes', 'orientation' => 'desktop', 'width' => 1, 'height' => 1,
            'file_path' => 'w/j.jpg', 'license' => 'cc0', 'status' => 'published']);
        $html = $this->get(route('wallpapers.category', 'airplanes'))->assertOk()->getContent();
        preg_match_all('#<details class="group rounded-lg"\s*open\s*>\s*<summary[^>]*>\s*([^<]+?)\s*<svg#', $html, $m);
        $this->assertSame(['Wallpapers'], $m[1]);
    }
}
