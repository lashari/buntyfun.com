<?php

namespace Tests\Feature;

use App\Models\ImportRun;
use App\Models\User;
use App\Models\Wallpaper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportSourcesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'admin'): User
    {
        return User::create(['name' => 'A', 'email' => $role.'@t.com', 'password' => 'password', 'role' => $role]);
    }

    private function jpeg(int $w, int $h): string
    {
        $img = imagecreatetruecolor($w, $h);
        ob_start();
        imagejpeg($img, null, 50);

        return ob_get_clean();
    }

    private function fakeNasa(): void
    {
        $item = fn ($id, $title) => [
            'data' => [['nasa_id' => $id, 'title' => $title, 'center' => 'JPL', 'photographer' => null]],
            'links' => [['href' => "https://images-assets.nasa.gov/image/{$id}/{$id}~thumb.jpg"]],
        ];
        Http::fake([
            'images-api.nasa.gov/search*' => Http::response(['collection' => [
                'items' => [$item('PIA1', 'Big galaxy'), $item('PIA2', 'Small moon'), $item('PIA3', 'Nebula')],
                'links' => [],
            ]]),
            'images-api.nasa.gov/asset/*' => function ($request) {
                preg_match('#asset/([^/?]+)#', $request->url(), $m);
                $id = $m[1];

                return Http::response(['collection' => ['items' => [
                    ['href' => "http://images-assets.nasa.gov/image/{$id}/{$id}~orig.jpg"],
                    ['href' => "http://images-assets.nasa.gov/image/{$id}/{$id}~thumb.jpg"],
                ]]]);
            },
            // closures: a fresh body per request (a shared fake response stream is read only once)
            'images-assets.nasa.gov/image/PIA2/*' => fn () => Http::response($this->jpeg(400, 300), 200, ['Content-Type' => 'image/jpeg']),
            'images-assets.nasa.gov/*' => fn () => Http::response($this->jpeg(1920, 1080), 200, ['Content-Type' => 'image/jpeg']),
        ]);
    }

    private function startRun(User $user, array $override = [])
    {
        return $this->actingAs($user)->post(route('admin.import.store'), [
            'content_type' => 'wallpaper', 'source' => 'nasa', 'query' => 'galaxy', 'category' => 'space',
            'target_count' => 10, 'orientation' => 'any', 'min_width' => 1280, ...$override,
        ]);
    }

    private function runToEnd(ImportRun $run): array
    {
        for ($i = 0; $i < 20; $i++) {
            $p = $this->postJson(route('admin.import.step', $run))->assertOk()->json();
            if ($p['status'] !== 'running') {
                return $p;
            }
        }
        $this->fail('import never finished');
    }

    public function test_guest_cannot_use_import(): void
    {
        $this->get(route('admin.import.index'))->assertRedirect(route('admin.login'));
    }

    public function test_import_page_lists_sources(): void
    {
        $this->actingAs($this->admin())->get(route('admin.import.index'))
            ->assertOk()->assertSee('NASA Image Library')->assertSee('Wikimedia Commons')->assertDontSee('UNSPLASH_ACCESS_KEY');
    }

    public function test_nasa_import_saves_in_review_skips_small_and_duplicates(): void
    {
        Storage::fake('public');
        $this->fakeNasa();
        $admin = $this->admin();

        $this->startRun($admin)->assertRedirect();
        $run = ImportRun::latest('id')->first();
        $this->assertSame(3, count($run->candidateList()));

        $p = $this->runToEnd($run);
        $this->assertSame(['done', 2, 1, 0], [$p['status'], $p['saved'], $p['skipped'], $p['failed']]);

        $w = Wallpaper::where('source_key', 'nasa:PIA1')->firstOrFail();
        $this->assertSame(['in_review', 'public_domain', 'space', 'desktop', 1920, 1080],
            [$w->status, $w->license, $w->category, $w->orientation, $w->width, $w->height]);
        $this->assertSame('Image: NASA / JPL', $w->attribution);
        Storage::disk('public')->assertExists($w->file_path);
        Storage::disk('public')->assertExists("wallpapers/thumbs/{$w->id}.jpg");
        $this->assertSame(640, getimagesize(Storage::disk('public')->path("wallpapers/thumbs/{$w->id}.jpg"))[0]);
        $this->assertSame(1600, getimagesize(Storage::disk('public')->path("wallpapers/previews/{$w->id}.jpg"))[0]);
        $this->assertStringContainsString('/thumbs/', $w->thumbUrl());

        // backfill endpoint: delete one thumb, the endpoint makes it again
        Storage::disk('public')->delete("wallpapers/thumbs/{$w->id}.jpg");
        $this->postJson(route('admin.import.thumbs'))->assertOk()->assertJson(['made' => 1, 'left' => 0]);
        Storage::disk('public')->assertExists("wallpapers/thumbs/{$w->id}.jpg");

        // same search again: nothing new is saved
        $this->startRun($admin);
        $p = $this->runToEnd(ImportRun::latest('id')->first());
        $this->assertSame([0, 3], [$p['saved'], $p['skipped']]);
        $this->assertSame(2, Wallpaper::count());

        // result page, CSV and ZIP
        $this->get(route('admin.import.show', $run))->assertOk()->assertSee('Result: 2 saved')->assertSee('Publish all 2');
        $csv = $this->get(route('admin.import.csv', $run))->assertOk()->streamedContent();
        $this->assertStringContainsString('nasa:PIA3', $csv);
        $this->get(route('admin.import.zip', $run))->assertOk()->assertDownload("import-{$run->id}-nasa.zip");
    }

    public function test_publish_all_and_undo(): void
    {
        Storage::fake('public');
        $this->fakeNasa();
        $admin = $this->admin();
        $this->startRun($admin);
        $run = ImportRun::latest('id')->first();
        $this->runToEnd($run);

        $editor = $this->admin('editor');
        $this->actingAs($editor)->post(route('admin.import.publish', $run))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.import.undo', $run))->assertForbidden();

        $first = Wallpaper::where('source_key', 'nasa:PIA1')->first();
        $first->update(['status' => 'published']);

        $this->actingAs($admin)->post(route('admin.import.undo', $run))->assertRedirect();
        $this->assertSame(['nasa:PIA1'], Wallpaper::pluck('source_key')->all()); // published one kept

        $this->actingAs($admin)->post(route('admin.import.publish', $run))->assertRedirect();
        $this->assertSame('published', $first->fresh()->status);
    }

    public function test_unsplash_is_not_offered(): void
    {
        $this->startRun($this->admin(), ['source' => 'unsplash'])->assertSessionHasErrors('source');
    }

    public function test_wikimedia_saves_only_reusable_licences(): void
    {
        Storage::fake('public');
        $page = fn ($id, $idx, $license, $name, $w = 3000, $h = 2000, $mime = 'image/jpeg') => [
            'pageid' => $id, 'index' => $idx, 'title' => "File:Mosque {$id}.jpg",
            'imageinfo' => [[
                'width' => $w, 'height' => $h, 'mime' => $mime,
                'url' => "https://upload.wikimedia.org/o/{$id}.jpg", 'thumburl' => "https://upload.wikimedia.org/t/{$id}.jpg",
                'descriptionurl' => "https://commons.wikimedia.org/wiki/File:Mosque_{$id}.jpg",
                'extmetadata' => ['License' => ['value' => $license], 'LicenseShortName' => ['value' => $name],
                    'Artist' => ['value' => '<a href="//x">Ali &amp; Co</a>']],
            ]],
        ];
        Http::fake([
            'commons.wikimedia.org/*' => Http::response(['query' => ['pages' => [
                $page(11, 1, 'cc-by-4.0', 'CC BY 4.0'),
                $page(12, 2, 'cc-by-sa-4.0', 'CC BY-SA 4.0'),
                $page(13, 3, 'pd', 'Public domain'),
                $page(14, 4, 'cc0', 'CC0', 800, 600),
                $page(15, 5, 'gfdl', 'GFDL 1.2'),
            ]]]),
            'upload.wikimedia.org/*' => fn () => Http::response($this->jpeg(2560, 1440), 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $this->startRun($this->admin(), ['source' => 'wikimedia', 'query' => 'mosque', 'category' => 'islamic', 'quality_only' => '1']);
        $p = $this->runToEnd(ImportRun::latest('id')->first());
        $this->assertSame([3, 2, 0], [$p['saved'], $p['skipped'], $p['failed']]);

        $w = Wallpaper::where('source_key', 'wikimedia:11')->firstOrFail();
        $this->assertSame(['cc_by', 'Ali & Co · CC BY 4.0 · via Wikimedia Commons', 'islamic', 2560, 1440],
            [$w->license, $w->attribution, $w->category, $w->width, $w->height]);
        $this->assertSame('public_domain', Wallpaper::where('source_key', 'wikimedia:13')->value('license'));
        $this->assertSame('cc_by_sa', Wallpaper::where('source_key', 'wikimedia:12')->value('license'));
        $this->assertFalse(Wallpaper::where('source_key', 'wikimedia:15')->exists()); // GFDL skipped
        Http::assertSent(fn ($r) => str_contains($r->header('User-Agent')[0] ?? '', 'BuntyFunImporter'));
        Http::assertSent(fn ($r) => str_contains(urldecode($r->url()), 'incategory:Quality_images mosque'));
    }
}
