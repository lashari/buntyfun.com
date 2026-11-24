<?php

namespace Tests\Feature;

use App\Content\MessagePages;
use App\Content\UrduPages;
use App\Models\UrduContent;
use Database\Seeders\UrduPagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrduPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_is_urdu_unique_and_well_formed(): void
    {
        $rows = collect(UrduPages::rows());
        $this->assertSame($rows->count(), $rows->pluck('slug')->unique()->count());
        $this->assertSame($rows->count(), $rows->pluck('body')->unique()->count(), 'duplicate line');
        $elsewhere = collect(MessagePages::rows())->pluck('body');
        foreach ($rows as $r) {
            $this->assertMatchesRegularExpression('/\p{Arabic}/u', $r['body'], $r['slug']);
            $this->assertNotContains($r['body'], $elsewhere, 'repeats a message page line: '.$r['slug']);
        }
        foreach (UrduPages::PAGES as $url => $page) {
            $this->assertSame(array_keys($page['sections']), array_keys($page['items']), $url);
            $this->assertGreaterThanOrEqual(UrduPages::MIN_FOR_INDEX, $rows->where('category', $page['category'])->count(), $url);
            $this->assertLessThanOrEqual(70, mb_strlen($page['title']), $url);
            $this->assertLessThanOrEqual(160, mb_strlen($page['description']), $url);
        }
    }

    public function test_pages_index_after_seeding_and_hub_links_them(): void
    {
        $this->get('/urdu-quotes')->assertOk()->assertSee('noindex', false);
        $this->get('/sitemap.xml')->assertDontSee('/urdu-quotes');

        $this->seed(UrduPagesSeeder::class);
        UrduContent::create(['category' => 'quotes', 'body' => 'پرانا قول', 'slug' => 'quotes-old-1', 'status' => 'published']);
        UrduContent::create(['category' => 'quotes', 'body' => 'مسودہ قول', 'slug' => 'quotes-draft-1', 'status' => 'draft']);

        foreach (array_keys(UrduPages::PAGES) as $url) {
            $this->get('/'.$url)->assertOk()->assertDontSee('noindex', false)->assertSee('font-urdu', false);
            $this->get('/sitemap.xml')->assertSee('/'.$url);
            $this->get('/ur/entertainment')->assertSee(route('urdu-content.page', $url));
        }
        $this->get('/urdu-quotes')->assertSee('id="life"', false)->assertSee('id="more"', false)
            ->assertSee('پرانا قول', false)->assertDontSee('مسودہ قول', false);
    }

    public function test_old_filter_redirects_and_single_line_links_to_topic(): void
    {
        $this->seed(UrduPagesSeeder::class);
        $this->get('/ur/entertainment?category=jokes')->assertStatus(301)->assertRedirect('/urdu-jokes');
        $this->get('/ur/entertainment?category=greetings')->assertOk();
        $this->get('/ur/entertainment/ur-lt-school-01')->assertOk()->assertSee(route('urdu-content.page', 'urdu-jokes'));
    }

    public function test_seeder_never_overwrites_existing_rows(): void
    {
        $this->seed(UrduPagesSeeder::class);
        $m = UrduContent::where('slug', 'ur-fm-parents-01')->first();
        $m->update(['status' => 'draft', 'body' => 'ترمیم']);
        $this->seed(UrduPagesSeeder::class);
        $m->refresh();
        $this->assertSame(['draft', 'ترمیم'], [$m->status, $m->body]);
    }
}
