<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallpaper;
use App\Services\Import\WallpaperDescriptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WallpaperDescriptionsTest extends TestCase
{
    use RefreshDatabase;

    private const LONG = 'The Pillars of Creation are towering columns of cold gas and dust inside the Eagle Nebula, about 6,500 light-years from Earth, where new stars are still forming deep inside the clouds.';

    private function wp(array $over = []): Wallpaper
    {
        static $i = 0;
        $i++;

        return Wallpaper::create($over + [
            'title' => "Wall {$i}", 'slug' => "wall-{$i}", 'category' => 'space', 'orientation' => 'desktop',
            'width' => 1920, 'height' => 1080, 'file_path' => "wallpapers/w{$i}.jpg", 'license' => 'public_domain', 'status' => 'published',
        ]);
    }

    public function test_clean_strips_html_links_credits_and_rejects_thin_text(): void
    {
        $clean = WallpaperDescriptions::clean('<p>'.self::LONG.' See https://nasa.gov/x</p> Image Credit: NASA/ESA', 'Pillars');
        $this->assertSame(self::LONG, $clean);
        $this->assertSame('', WallpaperDescriptions::clean('Pillars of Creation', 'Pillars of Creation'));
        $this->assertSame('', WallpaperDescriptions::clean('<b>A nice photo</b>'));
        $long = WallpaperDescriptions::clean(str_repeat('This sentence describes the sky over the hills. ', 40));
        $this->assertLessThanOrEqual(WallpaperDescriptions::MAX_CHARS, mb_strlen($long));
        $this->assertStringEndsWith('.', $long);
        $this->assertSame('the “Emerald Isle” is green', WallpaperDescriptions::fixMojibake(mb_convert_encoding('the “Emerald Isle” is green', 'UTF-8', 'Windows-1252')));
        $this->assertSame('café “ok”', WallpaperDescriptions::fixMojibake('café “ok”'));
    }

    public function test_page_is_noindex_without_caption_and_indexed_with_one(): void
    {
        $thin = $this->wp();
        $rich = $this->wp(['description' => self::LONG, 'source_key' => 'nasa:PIA1', 'source_url' => 'https://images.nasa.gov/details/PIA1']);

        $this->get('/wallpapers/'.$thin->slug)->assertOk()->assertSee('noindex', false)->assertDontSee('About this image');
        $this->get('/wallpapers/'.$rich->slug)->assertOk()->assertDontSee('noindex', false)
            ->assertSee('About this image')->assertSee('Description from the NASA Image Library')->assertSee('Pillars of Creation');

        $sitemap = $this->get('/sitemap.xml');
        $sitemap->assertSee('/wallpapers/'.$rich->slug)->assertDontSee('/wallpapers/'.$thin->slug.'<', false);
        $sitemap->assertSee('/wallpapers/c/space');
    }

    public function test_backfill_fetches_from_nasa_and_wikimedia_once(): void
    {
        Http::fake([
            'images-api.nasa.gov/*' => Http::response(['collection' => ['items' => [['data' => [['description' => self::LONG]]]]]]),
            'commons.wikimedia.org/*' => Http::response(['query' => ['pages' => [['imageinfo' => [['extmetadata' => ['ImageDescription' => ['value' => '<span>Short</span>']]]]]]]]),
        ]);
        $nasa = $this->wp(['source_key' => 'nasa:PIA1']);
        $wiki = $this->wp(['source_key' => 'wikimedia:123']);
        $own = $this->wp();

        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->postJson('/admin/import/descriptions')->assertOk()
            ->assertJson(['saved' => 1, 'none' => 1, 'left' => 0]);

        $this->assertSame(self::LONG, $nasa->fresh()->description);
        $this->assertSame('', $wiki->fresh()->description);
        $this->assertNull($own->fresh()->description);

        Http::assertSentCount(2);
        $this->actingAs($admin)->postJson('/admin/import/descriptions')->assertJson(['saved' => 0, 'left' => 0]);
        Http::assertSentCount(2);
    }

    public function test_admin_can_write_a_description(): void
    {
        $w = $this->wp();
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->put('/admin/wallpapers/'.$w->id, [
            'title' => $w->title, 'category' => 'space', 'orientation' => 'desktop', 'license' => 'public_domain',
            'status' => 'published', 'description' => self::LONG,
        ])->assertRedirect();
        $this->assertSame(self::LONG, $w->fresh()->description);
    }
}
