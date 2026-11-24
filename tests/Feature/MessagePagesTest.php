<?php

namespace Tests\Feature;

use App\Content\MessagePages;
use App\Models\Message;
use Database\Seeders\MessagePagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_is_complete_unique_and_well_formed(): void
    {
        $rows = collect(MessagePages::rows());
        $this->assertSame($rows->count(), $rows->pluck('slug')->unique()->count());
        $this->assertSame($rows->count(), $rows->pluck('body')->unique()->count(), 'duplicate message text');
        foreach (MessagePages::PAGES as $url => $page) {
            $this->assertArrayHasKey($page['category'], Message::CATEGORIES, $url);
            $this->assertSame(array_keys($page['sections']), array_keys($page['messages']), $url);
            $this->assertGreaterThanOrEqual(MessagePages::MIN_FOR_INDEX, $rows->where('category', $page['category'])->count(), $url);
            $this->assertLessThanOrEqual(70, mb_strlen($page['title']), $url.' title');
            $this->assertLessThanOrEqual(160, mb_strlen($page['description']), $url.' description');
            foreach ($page['messages'] as $section => $items) {
                foreach ($items as $i => $item) {
                    $this->assertSame($section, MessagePages::sectionOf($page, MessagePages::slug($page['prefix'], $section, $i + 1)));
                }
            }
        }
        // Urdu rows really are Urdu script, English rows are not.
        foreach ($rows as $r) {
            $this->assertSame($r['language'] === 'ur', (bool) preg_match('/\p{Arabic}/u', $r['body']), $r['slug']);
        }
    }

    public function test_pages_noindex_until_seeded_then_indexed_with_sections(): void
    {
        $this->get('/good-morning-messages')->assertOk()->assertSee('noindex', false)->assertSee('coming soon');
        $this->get('/sitemap.xml')->assertDontSee('/good-morning-messages');

        $this->seed(MessagePagesSeeder::class);
        Message::create(['category' => 'good_morning', 'language' => 'en', 'body' => 'An older morning message.', 'slug' => 'older-gm', 'status' => 'published']);
        Message::create(['category' => 'good_morning', 'language' => 'en', 'body' => 'A draft morning message.', 'slug' => 'draft-gm', 'status' => 'draft']);

        foreach (array_keys(MessagePages::PAGES) as $url) {
            $this->get('/'.$url)->assertOk()->assertDontSee('noindex', false);
            $this->get('/sitemap.xml')->assertSee('/'.$url);
        }
        $this->get('/good-morning-messages')
            ->assertSee('id="love"', false)->assertSee('id="urdu"', false)->assertSee('id="more"', false)
            ->assertSee('An older morning message.')->assertDontSee('A draft morning message.')
            ->assertSee('صبح بخیر', false);
    }

    public function test_condolence_page_has_no_ads(): void
    {
        config(['site.adsense_client' => 'ca-pub-0000000000000000', 'ads.client' => 'ca-pub-0000000000000000']);
        $this->seed(MessagePagesSeeder::class);
        $this->get('/condolence-messages')->assertOk()->assertDontSee('adsbygoogle', false);
    }

    public function test_old_category_filter_redirects_and_links_use_pages(): void
    {
        $this->seed(MessagePagesSeeder::class);
        $this->get('/messages?category=good_night')->assertRedirect('/good-night-messages')->assertStatus(301);
        $this->get('/messages?category=congratulations')->assertOk();
        $this->get('/messages')->assertSee(route('messages.page', 'eid-mubarak-messages'));
        $msg = Message::where('slug', 'msg-gm-love-01')->first();
        $this->get('/messages/'.$msg->slug)->assertSee(route('messages.page', 'good-morning-messages'));
        $this->get('/some-unknown-page')->assertNotFound();
    }

    public function test_seeder_never_overwrites_existing_rows(): void
    {
        $this->seed(MessagePagesSeeder::class);
        $m = Message::where('slug', 'msg-gw-friends-01')->first();
        $m->update(['status' => 'draft', 'body' => 'Edited.']);
        $this->seed(MessagePagesSeeder::class);
        $m->refresh();
        $this->assertSame(['draft', 'Edited.'], [$m->status, $m->body]);
    }
}
