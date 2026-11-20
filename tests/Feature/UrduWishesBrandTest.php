<?php

namespace Tests\Feature;

use App\Models\BirthdayMessage;
use App\Models\Category;
use Database\Seeders\UrduContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrduWishesBrandTest extends TestCase
{
    use RefreshDatabase;

    private function seedUrdu(): void
    {
        Category::firstOrCreate(['slug' => 'birthday-wishes'], ['department' => 'birthday', 'name' => 'Birthday Wishes']);
        $this->seed(UrduContentSeeder::class);
    }

    public function test_seeder_gives_every_relationship_hub_urdu_wishes_with_unique_slugs(): void
    {
        $this->seedUrdu();
        $ur = BirthdayMessage::where('language', 'ur')->get();
        $this->assertGreaterThanOrEqual(35, $ur->count());
        $this->assertSame($ur->count(), $ur->pluck('slug')->unique()->count());
        foreach (array_keys(\App\Content\BirthdayGuides::ALL) as $relationship) {
            $this->assertGreaterThanOrEqual(3, $ur->where('relationship', $relationship)->count(), $relationship);
        }
        // Re-running keeps the original four slugs where they were.
        $this->assertNotNull(BirthdayMessage::where('slug', 'husband-funny-ur-04')->first());
    }

    public function test_urdu_page_is_noindex_until_ten_wishes_then_indexed_and_in_sitemap(): void
    {
        Category::firstOrCreate(['slug' => 'birthday-wishes'], ['department' => 'birthday', 'name' => 'Birthday Wishes']);
        $this->get('/birthday-wishes/urdu')->assertOk()->assertSee('noindex', false);
        $this->get('/sitemap.xml')->assertDontSee('/birthday-wishes/urdu');

        $this->seedUrdu();
        BirthdayMessage::query()->update(['status' => 'published']);
        $this->get('/birthday-wishes/urdu')->assertOk()->assertDontSee('noindex', false)
            ->assertSee('سالگرہ مبارک امی جان', false)->assertSee('fonts.bunny.net', false)->assertSee('id="mother"', false);
        $this->get('/sitemap.xml')->assertSee('/birthday-wishes/urdu');
        $this->get('/birthday-wishes')->assertSee(route('birthday-messages.urdu'), false);
    }

    public function test_layout_shows_logo_favicons_and_default_share_image(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('images/brand/buntyfun-logo.svg', false)
            ->assertSee('images/brand/apple-touch-icon.png', false)
            ->assertSee('images/brand/og-default.png', false);
        foreach (['images/brand/buntyfun-logo.svg', 'images/brand/buntyfun-mark.svg', 'images/brand/icon.svg', 'images/brand/og-default.png', 'images/brand/apple-touch-icon.png', 'favicon.ico'] as $f) {
            $this->assertFileExists(public_path($f));
        }
    }
}
