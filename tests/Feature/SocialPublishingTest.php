<?php

namespace Tests\Feature;

use App\Models\AutomationJob;
use App\Models\Message;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Models\User;
use App\Services\Automation\AutomationRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SocialPublishingTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::create(['name' => $role, 'email' => $role.'@t.com', 'password' => 'p', 'role' => $role]);
    }

    private function accounts(): array
    {
        $fb = SocialAccount::create(['platform' => 'facebook', 'external_id' => '111', 'name' => 'BuntyFun', 'access_token' => 'PAGE-TOKEN-SECRET']);
        $ig = SocialAccount::create(['platform' => 'instagram', 'external_id' => '222', 'name' => 'BuntyFun', 'username' => 'buntyfun.official', 'access_token' => 'PAGE-TOKEN-SECRET', 'page_id' => '111']);

        return [$fb, $ig];
    }

    public function test_connect_flow_stores_encrypted_page_and_instagram_tokens(): void
    {
        Config::set('services.meta.app_secret', 'app-secret');
        $super = $this->user('super_admin');

        $this->actingAs($super)->get(route('admin.social.meta.connect'))->assertRedirectContains('facebook.com');
        $state = session('meta_oauth_state');
        $this->assertNotEmpty($state);

        Http::fake([
            'graph.facebook.com/*/oauth/access_token' => Http::sequence()->push(['access_token' => 'SHORT'])->push(['access_token' => 'LONG']),
            'graph.facebook.com/*/me/accounts*' => Http::response(['data' => [[
                'id' => '111', 'name' => 'BuntyFun', 'access_token' => 'PAGE-TOKEN-SECRET',
                'instagram_business_account' => ['id' => '222', 'username' => 'buntyfun.official'],
            ]]]),
        ]);

        $this->actingAs($super)->get(route('admin.social.meta.callback', ['code' => 'abc', 'state' => 'wrong']))->assertSessionHasErrors();
        $this->assertSame(0, SocialAccount::count());

        $this->actingAs($super)->get(route('admin.social.meta.connect'));
        $this->actingAs($super)->get(route('admin.social.meta.callback', ['code' => 'abc', 'state' => session('meta_oauth_state')]))->assertSessionHas('status');

        $this->assertSame(2, SocialAccount::count());
        $this->assertSame('@buntyfun.official', SocialAccount::where('platform', 'instagram')->first()->label());
        $this->assertStringNotContainsString('PAGE-TOKEN', DB::table('social_accounts')->value('access_token'));
        $this->assertSame('PAGE-TOKEN-SECRET', SocialAccount::first()->token());
        // Tokens never travel in a URL.
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'access_token=') || str_contains($r->url(), 'client_secret'));
        // And never appear in the admin page.
        $this->actingAs($super)->get(route('admin.social.index'))->assertOk()->assertDontSee('PAGE-TOKEN');
    }

    public function test_admin_only_editor_drafts_and_instagram_needs_image(): void
    {
        [$fb, $ig] = $this->accounts();
        $editor = $this->user('editor');

        $this->actingAs($editor)->post(route('admin.social.store'), ['accounts' => [$fb->id], 'caption' => 'Hi', 'action' => 'approve'])->assertForbidden();
        $this->actingAs($editor)->post(route('admin.social.store'), ['accounts' => [$fb->id], 'caption' => 'Hi', 'link' => 'https://buntyfun.com/messages/x', 'action' => 'draft'])->assertRedirect();
        $post = SocialPost::first();
        $this->assertSame('draft', $post->status);
        $this->assertStringContainsString('utm_source=facebook', $post->link);
        $this->assertStringContainsString('utm_content=post'.$post->id, $post->link);
        $this->actingAs($editor)->post(route('admin.social.approve', $post))->assertForbidden();

        $admin = $this->user('admin');
        $this->actingAs($admin)->post(route('admin.social.store'), ['accounts' => [$ig->id], 'caption' => 'Hi', 'action' => 'approve'])->assertSessionHasErrors();
        $this->assertSame(1, SocialPost::count());
    }

    public function test_cron_publishes_facebook_and_instagram_once(): void
    {
        [$fb, $ig] = $this->accounts();
        Http::fake([
            'graph.facebook.com/*/111/feed' => Http::response(['id' => '111_999']),
            'graph.facebook.com/*/111_999*' => Http::response(['permalink_url' => 'https://facebook.com/111/posts/999']),
            'graph.facebook.com/*/222/media_publish' => Http::response(['id' => 'IGMEDIA1']),
            'graph.facebook.com/*/222/media' => Http::response(['id' => 'CONTAINER1']),
            'graph.facebook.com/*/CONTAINER1*' => Http::response(['status_code' => 'FINISHED']),
            'graph.facebook.com/*/IGMEDIA1*' => Http::response(['permalink' => 'https://instagram.com/p/abc']),
        ]);
        SocialPost::create(['platform' => 'facebook', 'social_account_id' => $fb->id, 'caption' => 'Eid Mubarak!', 'link' => 'https://buntyfun.com/messages/eid?utm_source=facebook', 'status' => 'approved']);
        SocialPost::create(['platform' => 'instagram', 'social_account_id' => $ig->id, 'caption' => 'Eid Mubarak!', 'image_url' => 'https://buntyfun.com/storage/social/eid.jpg', 'status' => 'approved']);
        SocialPost::create(['platform' => 'facebook', 'social_account_id' => $fb->id, 'caption' => 'Later', 'status' => 'approved', 'scheduled_at' => now()->addDay()]);
        SocialPost::create(['platform' => 'facebook', 'social_account_id' => $fb->id, 'caption' => 'Not approved', 'status' => 'draft']);

        $job = AutomationJob::where('key', 'social_publish')->first();
        $run = app(AutomationRunner::class)->run($job, 'test');

        $this->assertSame('success', $run->status, $run->message);
        $this->assertSame(2, $run->items);
        $this->assertSame('https://facebook.com/111/posts/999', SocialPost::where('platform', 'facebook')->where('status', 'published')->first()->permalink);
        $this->assertSame('https://instagram.com/p/abc', SocialPost::where('platform', 'instagram')->first()->permalink);
        $this->assertSame(2, SocialPost::where('status', 'approved')->count() + SocialPost::where('status', 'draft')->count());
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/111/feed') && $r['access_token'] === 'PAGE-TOKEN-SECRET' && $r['message'] === 'Eid Mubarak!');

        $again = app(AutomationRunner::class)->run($job, 'test');
        $this->assertSame('skipped', $again->status);
        Http::assertSentCount(6);
    }

    public function test_temporary_error_retries_then_fails_and_permission_error_fails_at_once(): void
    {
        [$fb] = $this->accounts();
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Service temporarily unavailable', 'code' => 2]], 503)]);
        $post = SocialPost::create(['platform' => 'facebook', 'social_account_id' => $fb->id, 'caption' => 'x', 'status' => 'approved']);

        app(\App\Services\Social\SocialPublisher::class)->publish($post->id);
        $post->refresh();
        $this->assertSame('approved', $post->status, (string) $post->error);
        $this->assertTrue($post->scheduled_at->isFuture());
        $this->assertStringContainsString('temporarily', $post->error);

        $post->update(['attempts' => 2, 'scheduled_at' => now()]);
        app(\App\Services\Social\SocialPublisher::class)->publish($post->id);
        $this->assertSame('failed', $post->fresh()->status);
    }

    public function test_permission_error_fails_at_once_and_flags_the_account(): void
    {
        [$fb] = $this->accounts();
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => '(#200) Requires pages_manage_posts permission', 'code' => 200]], 403)]);
        $p2 = SocialPost::create(['platform' => 'facebook', 'social_account_id' => $fb->id, 'caption' => 'y', 'status' => 'approved']);
        app(\App\Services\Social\SocialPublisher::class)->publish($p2->id);
        $this->assertSame('failed', $p2->fresh()->status);
        $this->assertStringContainsString('pages_manage_posts', $fb->fresh()->last_error);
    }

    public function test_post_prefill_from_content_and_pages_render(): void
    {
        $this->accounts();
        $m = Message::create(['category' => 'eid', 'language' => 'en', 'title' => 'Eid Joy', 'body' => 'Eid Mubarak to you and yours.', 'slug' => 'eid-joy', 'status' => 'published']);
        $admin = $this->user('super_admin');

        $this->actingAs($admin)->get(route('admin.social.create', ['type' => 'message', 'id' => $m->id]))
            ->assertOk()->assertSee('Eid Mubarak to you and yours.')->assertSee(route('messages.show', $m), false);
        $this->actingAs($admin)->get(route('admin.social.index'))->assertOk()->assertSee('Connected accounts');
        $this->actingAs($admin)->get(route('admin.automation.calendar'))->assertOk();
    }

    public function test_compose_form_only_preticks_buntyfun_accounts(): void
    {
        [$fb, $ig] = $this->accounts();
        $other = SocialAccount::create(['platform' => 'facebook', 'external_id' => '333', 'name' => 'DueClix', 'access_token' => 'X']);
        $html = $this->actingAs($this->user('super_admin'))->get(route('admin.social.create'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/value="'.$fb->id.'"\s+checked/', $html);
        $this->assertMatchesRegularExpression('/value="'.$ig->id.'"\s+checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="'.$other->id.'"\s+checked/', $html);
    }
}
