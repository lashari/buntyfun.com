<?php

namespace Tests\Feature;

use App\Models\Wallpaper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function wallpapers(string $category, int $n): void
    {
        for ($i = 1; $i <= $n; $i++) {
            Wallpaper::create([
                'title' => "{$category} {$i}", 'slug' => "{$category}-{$i}-x", 'category' => $category,
                'orientation' => 'desktop', 'width' => 1920, 'height' => 1080, 'file_path' => "wallpapers/{$category}-{$i}.jpg",
                'license' => 'public_domain', 'status' => 'published',
            ]);
        }
    }

    public function test_header_has_section_menus_with_plain_links(): void
    {
        $this->wallpapers('cars', 2);

        $html = $this->get(route('home'))->assertOk()->getContent();

        foreach (['Games', 'Quizzes', 'Wishes &amp; Messages', 'Islamic', 'Urdu', 'Wallpapers', 'AI Tools'] as $label) {
            $this->assertStringContainsString('>'.$label.'</a>', $html, $label);
        }
        foreach ([route('islamic.allah-names'), route('duas.index'), route('poetry.index'), route('urdu-content.page', 'urdu-quotes'),
            route('birthday-messages.index'), route('wallpapers.category', 'cars'), route('daily')] as $url) {
            $this->assertStringContainsString('href="'.$url.'"', $html, $url);
        }
        // dropdown buttons are real buttons with aria-expanded, and the mobile menu uses native <details>
        $this->assertStringContainsString('aria-controls="nav-islamic"', $html);
        $this->assertStringContainsString('<details', $html);
    }

    public function test_hubs_have_matching_visible_and_structured_breadcrumbs(): void
    {
        foreach (['games.index' => 'Games', 'quizzes.index' => 'Quizzes', 'birthday-messages.index' => 'Birthday Wishes',
            'name-entries.index' => 'Islamic Names', 'ai-tools.index' => 'AI Fun Studio'] as $route => $label) {
            $html = $this->get(route($route))->assertOk()->getContent();
            $this->assertStringContainsString('"@type":"BreadcrumbList"', $html, $route);
            $this->assertStringContainsString('"name":"'.$label.'"', $html, $route);
            $this->assertStringContainsString('<span class="text-slate-700" aria-current="page">'.$label.'</span>', $html, $route);
        }
    }

    public function test_hub_pages_link_the_rest_of_their_section(): void
    {
        $this->get(route('name-entries.index'))->assertOk()
            ->assertSee('Explore Islamic')->assertSee('href="'.route('islamic.allah-names').'"', false);
        $this->get(route('poetry.index'))->assertOk()
            ->assertSee('Explore Urdu')->assertSee('href="'.route('urdu-content.page', 'urdu-jokes').'"', false);
    }

    public function test_wallpaper_page_links_more_from_its_category(): void
    {
        $this->wallpapers('space', 9);
        $first = Wallpaper::where('slug', 'space-9-x')->first();

        $this->get(route('wallpapers.show', $first))->assertOk()
            ->assertSee('More space wallpapers')
            ->assertSee('href="'.route('wallpapers.show', 'space-8-x').'"', false)
            ->assertSee('href="'.route('wallpapers.show', 'space-3-x').'"', false);
    }
}
