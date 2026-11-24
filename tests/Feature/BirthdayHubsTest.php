<?php

namespace Tests\Feature;

use App\Content\BirthdayGuides;
use App\Content\BirthdayWishesMore;
use App\Models\BirthdayMessage;
use Database\Seeders\BirthdayWishesMoreSeeder;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BirthdayHubsTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_is_unique_has_guides_and_known_tones(): void
    {
        $rows = collect(BirthdayWishesMore::rows());
        $this->assertSame($rows->count(), $rows->pluck('slug')->unique()->count());
        $this->assertSame($rows->count(), $rows->pluck('body')->unique()->count(), 'duplicate wish text');
        foreach ($rows as $r) {
            $this->assertContains($r['tone'], ['funny', 'emotional', 'short', 'formal', 'heartfelt'], $r['slug']);
            $this->assertNotNull(BirthdayGuides::for($r['relationship']), 'no guide for '.$r['relationship']);
            $this->assertSame($r['language'] === 'ur', (bool) preg_match('/\p{Arabic}/u', $r['body']), $r['slug']);
        }
        foreach (array_keys(BirthdayWishesMore::WISHES) as $rel) {
            $this->assertGreaterThanOrEqual(13, $rows->where('relationship', $rel)->count(), $rel);
            $this->assertGreaterThanOrEqual(2, $rows->where('relationship', $rel)->where('language', 'ur')->count(), $rel.' urdu');
        }
    }

    public function test_new_hubs_appear_after_seeding_and_existing_rows_untouched(): void
    {
        $this->seed(CategorySeeder::class);
        $this->get('/birthday-wishes/for/boss')->assertNotFound();

        $this->seed(BirthdayWishesMoreSeeder::class);
        foreach (['boss', 'daughter', 'son', 'grandfather', 'grandmother', 'girlfriend', 'boyfriend', 'mother', 'best-friend'] as $slug) {
            $this->get('/birthday-wishes/for/'.$slug)->assertOk()->assertDontSee('noindex', false);
            $this->get('/sitemap.xml')->assertSee('/birthday-wishes/for/'.$slug);
        }
        $this->get('/birthday-wishes/for/boss')->assertSee('Birthday Wishes for Your Boss')
            ->assertSee('Formal birthday wishes for your boss')->assertSee('font-urdu', false);
        $this->get('/birthday-wishes/for/child')->assertSee('Birthday Wishes for Kids');

        $w = BirthdayMessage::where('slug', 'bw-boss-formal-01')->first();
        $w->update(['status' => 'draft', 'body' => 'Edited.']);
        $this->seed(BirthdayWishesMoreSeeder::class);
        $w->refresh();
        $this->assertSame(['draft', 'Edited.'], [$w->status, $w->body]);
    }
}
