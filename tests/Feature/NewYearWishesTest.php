<?php

namespace Tests\Feature;

use App\Content\NewYearWishes;
use App\Models\Message;
use Database\Seeders\NewYearWishesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewYearWishesTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_has_every_section_and_unique_slugs(): void
    {
        $rows = collect(NewYearWishes::rows());
        $this->assertGreaterThanOrEqual(50, $rows->count());
        $this->assertSame($rows->count(), $rows->pluck('slug')->unique()->count());
        foreach (array_keys(NewYearWishes::SECTIONS) as $section) {
            $this->assertGreaterThanOrEqual(6, $rows->filter(fn ($r) => NewYearWishes::sectionOf($r['slug']) === $section)->count(), $section);
        }
        $this->assertTrue($rows->filter(fn ($r) => $r['language'] === 'ur')->every(fn ($r) => NewYearWishes::sectionOf($r['slug']) === 'urdu'));
        $this->assertNull(NewYearWishes::sectionOf('warm-wishes-for-a-bright-new-year-vrssy'));
    }

    public function test_page_is_noindex_and_out_of_sitemap_until_seeded(): void
    {
        $this->get('/new-year-wishes')->assertOk()->assertSee('noindex', false)->assertSee('coming soon');
        $this->get('/sitemap.xml')->assertDontSee('/new-year-wishes');
    }

    public function test_seeded_page_groups_sections_and_shows_other_new_year_messages(): void
    {
        $this->seed(NewYearWishesSeeder::class);
        Message::create(['category' => 'new_year', 'language' => 'en', 'body' => 'An auto written wish.', 'slug' => 'auto-wish-abc12', 'status' => 'published']);
        Message::create(['category' => 'new_year', 'language' => 'en', 'body' => 'A draft wish.', 'slug' => 'draft-wish-x', 'status' => 'draft']);

        $res = $this->get('/new-year-wishes')->assertOk()->assertDontSee('noindex', false);
        $res->assertSee('New Year Wishes '.NewYearWishes::YEAR)
            ->assertSee('id="family"', false)->assertSee('id="urdu"', false)->assertSee('id="more"', false)
            ->assertSee('An auto written wish.')->assertDontSee('A draft wish.')
            ->assertSee('نیا سال مبارک', false);
        $this->get('/sitemap.xml')->assertSee('/new-year-wishes');
        $this->get('/messages')->assertSee(route('messages.new-year'));
    }

    public function test_seeder_never_overwrites_or_unpublishes_existing_rows(): void
    {
        $this->seed(NewYearWishesSeeder::class);
        $first = Message::where('slug', NewYearWishes::slug('family', 1))->first();
        $first->update(['status' => 'draft', 'body' => 'Edited by admin.']);

        $this->seed(NewYearWishesSeeder::class);
        $first->refresh();
        $this->assertSame('draft', $first->status);
        $this->assertSame('Edited by admin.', $first->body);
        $this->assertSame(count(NewYearWishes::rows()), Message::where('category', 'new_year')->count());
    }
}
