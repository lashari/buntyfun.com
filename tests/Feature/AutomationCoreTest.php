<?php

namespace Tests\Feature;

use App\Models\AiDraft;
use App\Models\AutomationJob;
use App\Models\AutomationRun;
use App\Models\BirthdayMessage;
use App\Models\Category;
use App\Models\ContentMeta;
use App\Models\ContentView;
use App\Models\Message;
use App\Models\NameEntry;
use App\Models\Setting;
use App\Models\User;
use App\Services\Automation\AutomationRunner;
use App\Services\Content\DraftApprover;
use App\Services\Content\DraftGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AutomationCoreTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGemini(array $json, int $in = 400, int $out = 200): void
    {
        Config::set('ai.gemini.key', 'test-key');
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode($json, JSON_UNESCAPED_UNICODE)]]]]],
            'usageMetadata' => ['promptTokenCount' => $in, 'candidatesTokenCount' => $out],
        ])]);
    }

    private function goodMessage(array $over = []): array
    {
        return $over + [
            'title' => 'A Morning Hello',
            'body' => 'Good morning to someone who makes ordinary days feel lighter. I hope your tea is hot, your inbox is kind, and something small makes you laugh before lunch.',
            'seo_title' => 'Warm Good Morning Message to Send Today',
            'seo_description' => 'An original good morning message you can copy and share on WhatsApp with a friend or family member to brighten their day.',
            'faq' => [['q' => 'Can I edit it?', 'a' => 'Yes, add their name before you send it.']],
            'social_caption' => 'Send this to someone today. #GoodMorning',
            'image_brief' => 'A cup of tea by a sunny window.',
        ];
    }

    private function admin(string $role = 'super_admin'): User
    {
        return User::create(['name' => $role, 'email' => $role.'@test.com', 'password' => 'password', 'role' => $role]);
    }

    public function test_generates_a_checked_draft_with_token_cost(): void
    {
        $this->fakeGemini($this->goodMessage());

        $draft = app(DraftGenerator::class)->generate('message', ['category' => 'good_morning', 'language' => 'en']);

        $this->assertSame('generated', $draft->status);
        $this->assertSame('good_morning', $draft->payload['category']);
        $this->assertFalse($draft->hasBlockingFailure(), json_encode($draft->checks));
        $this->assertGreaterThanOrEqual(80, $draft->quality_score);
        $this->assertSame(400, $draft->input_tokens);
        // 400 * 0.10/1M + 200 * 0.40/1M
        $this->assertEqualsWithDelta(0.00012, $draft->cost_usd, 0.0000001);
    }

    public function test_bad_json_and_missing_key_become_failed_drafts_not_crashes(): void
    {
        Config::set('ai.gemini.key', 'k');
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'sorry, no']]]]]])]);
        $draft = app(DraftGenerator::class)->generate('message', ['category' => 'eid', 'language' => 'en']);
        $this->assertSame('failed', $draft->status);
        $this->assertStringContainsString('JSON', $draft->error);

        Config::set('ai.gemini.key', null);
        $draft = app(DraftGenerator::class)->generate('message', ['category' => 'eid', 'language' => 'en']);
        $this->assertSame('failed', $draft->status);
    }

    public function test_duplicate_of_existing_content_is_blocked(): void
    {
        Message::create(['category' => 'good_morning', 'language' => 'en', 'body' => $this->goodMessage()['body'], 'slug' => 'gm-1', 'status' => 'published']);
        $this->fakeGemini($this->goodMessage());

        $draft = app(DraftGenerator::class)->generate('message', ['category' => 'good_morning', 'language' => 'en']);

        $this->assertTrue($draft->hasBlockingFailure());
        $dup = collect($draft->checks)->firstWhere('key', 'duplicate');
        $this->assertFalse($dup['ok']);
    }

    public function test_ai_filler_and_links_are_blocked(): void
    {
        $this->fakeGemini($this->goodMessage(['body' => 'As an AI, here is a lovely message for you, visit https://spam.example.com today and have a really wonderful morning friend.']));
        $draft = app(DraftGenerator::class)->generate('message', ['category' => 'good_morning', 'language' => 'en']);
        $failed = collect($draft->checks)->where('ok', false)->pluck('key')->all();
        $this->assertContains('no_links', $failed);
        $this->assertContains('no_ai_phrases', $failed);
    }

    public function test_urdu_draft_must_be_in_urdu_script(): void
    {
        $this->fakeGemini($this->goodMessage(['body' => 'Aap ko salgirah bohat bohat mubarak ho, Allah aap ko hamesha khush rakhe aur har khushi de.']));
        $draft = app(DraftGenerator::class)->generate('urdu', ['category' => 'greetings']);
        $this->assertFalse(collect($draft->checks)->firstWhere('key', 'urdu_script')['ok']);
    }

    public function test_urdu_draft_in_urdu_script_passes(): void
    {
        $this->fakeGemini($this->goodMessage(['body' => 'آپ کو سالگرہ بہت بہت مبارک ہو، اللہ آپ کو ہمیشہ خوش رکھے اور آپ کی ہر دعا قبول کرے۔']));
        $draft = app(DraftGenerator::class)->generate('urdu', ['category' => 'greetings']);
        $this->assertTrue(collect($draft->checks)->firstWhere('key', 'urdu_script')['ok']);
    }

    public function test_approve_now_publishes_once_and_never_twice(): void
    {
        $this->fakeGemini($this->goodMessage());
        $draft = app(DraftGenerator::class)->generate('message', ['category' => 'good_morning', 'language' => 'en']);
        $admin = $this->admin();

        app(DraftApprover::class)->approve($draft, $admin, 'now');

        $this->assertSame(1, Message::where('status', 'published')->count());
        $msg = Message::first();
        $this->assertSame('Warm Good Morning Message to Send Today', $msg->meta->seo_title);
        $this->assertSame('approved', $draft->fresh()->status);

        $this->expectException(RuntimeException::class);
        app(DraftApprover::class)->approve($draft->fresh(), $admin, 'now');
    }

    public function test_editor_cannot_publish_or_schedule_but_can_send_to_review(): void
    {
        $this->fakeGemini($this->goodMessage());
        $draft = app(DraftGenerator::class)->generate('message', ['category' => 'good_morning', 'language' => 'en']);
        $editor = $this->admin('editor');

        $this->actingAs($editor)->post(route('admin.ai-studio.approve', $draft), ['mode' => 'now'])->assertSessionHasErrors();
        $this->assertSame(0, Message::count());

        $this->actingAs($editor)->post(route('admin.ai-studio.approve', $draft), ['mode' => 'review'])->assertRedirect();
        $this->assertSame('in_review', Message::first()->status);
    }

    public function test_scheduled_item_is_published_by_cron_job_exactly_once(): void
    {
        $this->fakeGemini($this->goodMessage());
        $draft = app(DraftGenerator::class)->generate('message', ['category' => 'good_morning', 'language' => 'en']);
        app(DraftApprover::class)->approve($draft, $this->admin(), 'schedule', now()->addHour());
        $this->assertSame('in_review', Message::first()->status);

        $job = AutomationJob::where('key', 'publish_scheduled')->first();
        $run = app(AutomationRunner::class)->run($job, 'test');
        $this->assertSame('skipped', $run->status); // not due yet

        $this->travel(2)->hours();
        $run = app(AutomationRunner::class)->run($job, 'test');
        $this->assertSame('success', $run->status);
        $this->assertSame(1, $run->items);
        $this->assertSame('published', Message::first()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'auto:published_scheduled', 'user_id' => null]);

        $again = app(AutomationRunner::class)->run($job, 'test');
        $this->assertSame(0, $again->items);
        $this->assertSame(1, \App\Models\AuditLog::where('action', 'auto:published_scheduled')->count());
    }

    public function test_name_draft_needs_approved_reference_and_human_verification(): void
    {
        $this->fakeGemini(['name_en' => 'Zayan', 'name_ar' => 'زيان', 'name_ur' => 'زیان', 'meaning' => 'Beauty, grace', 'origin' => 'Arabic',
            'confident' => true, 'suggested_reference' => 'Dictionary of Muslim Names', 'seo_title' => 'Zayan Name Meaning in Urdu and English',
            'seo_description' => 'Zayan is a Muslim boy name. Read its meaning, origin and Arabic and Urdu spelling on BuntyFun names directory.']);
        $draft = app(DraftGenerator::class)->generate('name', ['gender' => 'male']);
        $this->assertTrue($draft->hasBlockingFailure());

        $admin = $this->admin();
        try {
            app(DraftApprover::class)->approve($draft, $admin, 'now');
            $this->fail('Unverified name was approved');
        } catch (RuntimeException) {
        }
        $this->assertSame(0, NameEntry::count());

        Setting::put('approved_name_references', ['Dictionary of Muslim Names']);
        $this->actingAs($admin)->put(route('admin.ai-studio.update', $draft), array_merge(collect($draft->payload)->map(fn ($v) => is_array($v) ? '' : $v)->all(), ['verified_by_admin' => '1']))->assertRedirect();
        $draft->refresh();
        $this->assertFalse($draft->hasBlockingFailure(), json_encode($draft->checks));
        $this->assertSame($admin->id, $draft->payload['verified_by']);

        app(DraftApprover::class)->approve($draft, $admin, 'now');
        $this->assertSame('published', NameEntry::first()->status);
    }

    public function test_failing_job_retries_with_backoff_and_lock_blocks_parallel_run(): void
    {
        Config::set('ai.gemini.key', null);
        $job = AutomationJob::where('key', 'generate_drafts')->first();

        $run = app(AutomationRunner::class)->run($job, 'test');
        $this->assertSame('failed', $run->status);
        $job->refresh();
        $this->assertSame(1, $job->consecutive_failures);
        $this->assertTrue($job->next_run_at->between(now()->addMinutes(9), now()->addMinutes(11)));

        $lock = Cache::lock('automation:generate_drafts', 60);
        $this->assertTrue($lock->get());
        $this->assertNull(app(AutomationRunner::class)->run($job, 'test'));
        $lock->release();
    }

    public function test_daily_job_writes_drafts_by_priority_and_respects_budget(): void
    {
        $this->fakeGemini($this->goodMessage());
        Setting::put('drafts_per_day', 2);
        Setting::put('type_weights', ['birthday' => 0, 'message' => 1, 'urdu' => 0, 'name' => 0]);
        $job = AutomationJob::where('key', 'generate_drafts')->first();

        $run = app(AutomationRunner::class)->run($job, 'test');
        $this->assertSame('success', $run->status, $run->message);
        $this->assertSame(2, AiDraft::where('content_type', 'message')->count());
        $this->assertSame(0, Message::count()); // drafts only, nothing published

        Setting::put('ai_monthly_budget_usd', 0.0001);
        $run = app(AutomationRunner::class)->run($job, 'test');
        $this->assertSame('skipped', $run->status);
        $this->assertStringContainsString('budget', $run->message);
    }

    public function test_auto_publish_publishes_only_fully_green_drafts(): void
    {
        $this->admin();
        $this->fakeGemini($this->goodMessage());
        Setting::put('auto_publish', true);
        Setting::put('drafts_per_day', 2);
        Setting::put('type_weights', ['birthday' => 0, 'message' => 1, 'urdu' => 0, 'name' => 0]);
        $job = AutomationJob::where('key', 'generate_drafts')->first();

        $run = app(AutomationRunner::class)->run($job, 'test');

        $this->assertSame('success', $run->status, $run->message);
        // first draft is all green -> published; the identical second one is a duplicate -> waits
        $this->assertSame(1, Message::where('status', 'published')->count());
        $this->assertSame(1, AiDraft::where('status', 'approved')->count());
        $this->assertSame(1, AiDraft::where('status', 'generated')->count());
        $this->assertStringContainsString('auto-published 1', $run->message);
    }

    public function test_auto_publish_off_by_default_and_never_for_names(): void
    {
        $this->admin();
        $this->fakeGemini($this->goodMessage());
        Setting::put('drafts_per_day', 1);
        Setting::put('type_weights', ['birthday' => 0, 'message' => 1, 'urdu' => 0, 'name' => 0]);
        $job = AutomationJob::where('key', 'generate_drafts')->first();
        app(AutomationRunner::class)->run($job, 'test');
        $this->assertSame(0, Message::count());

        $this->assertNotContains('name', \App\Services\Automation\Tasks\GenerateDraftsTask::AUTO_TYPES);
    }

    public function test_super_admin_can_switch_auto_publish_on_and_off(): void
    {
        $admin = $this->admin();
        $base = ['drafts_per_day' => 6, 'type_weights' => ['birthday' => 3, 'message' => 3, 'urdu' => 1, 'name' => 0],
                 'ai_monthly_budget_usd' => 5, 'ai_price_input_per_m' => 0.1, 'ai_price_output_per_m' => 0.4];
        $this->actingAs($admin)->put(route('admin.automation.settings.save'), $base + ['auto_publish' => '1'])->assertRedirect();
        $this->assertTrue((bool) Setting::get('auto_publish'));
        $this->actingAs($admin)->put(route('admin.automation.settings.save'), $base + ['auto_publish' => '0'])->assertRedirect();
        $this->assertFalse((bool) Setting::get('auto_publish'));
    }

    public function test_run_due_schedules_new_jobs_instead_of_firing_on_deploy(): void
    {
        $runs = app(AutomationRunner::class)->runDue();
        $this->assertSame([], $runs);
        $this->assertSame(0, AutomationJob::whereNull('next_run_at')->where('enabled', true)->count());
        $this->assertSame(0, AutomationRun::count());
    }

    public function test_public_messages_seo_sitemap_feed_and_robots(): void
    {
        $m = Message::create(['category' => 'eid', 'language' => 'en', 'title' => 'Eid Joy', 'body' => 'Eid Mubarak to you and your family, may your table be full and your home full of laughter.', 'slug' => 'eid-joy', 'status' => 'published']);
        ContentMeta::create(['metable_type' => 'message', 'metable_id' => $m->id, 'seo_title' => 'Eid Mubarak Message for Family',
            'faq' => [['q' => 'When to send?', 'a' => 'On Eid morning.']]]);
        $hidden = Message::create(['category' => 'eid', 'language' => 'en', 'body' => 'Hidden one with enough words to be a message here.', 'slug' => 'hidden-eid', 'status' => 'published']);
        ContentMeta::create(['metable_type' => 'message', 'metable_id' => $hidden->id, 'noindex' => true]);
        $condolence = Message::create(['category' => 'condolence', 'language' => 'en', 'body' => 'Thinking of you.', 'slug' => 'sorry', 'status' => 'published']);

        $this->get(route('messages.index'))->assertOk()->assertSee('Eid Joy');
        $page = $this->get(route('messages.show', $m))->assertOk();
        $page->assertSee('<title>Eid Mubarak Message for Family — BuntyFun</title>', false);
        $page->assertSee('"@type":"FAQPage"', false);
        $page->assertSee('"@type":"BreadcrumbList"', false);
        $page->assertSee('og:title', false);
        $this->get(route('messages.show', $hidden))->assertSee('noindex', false);

        Config::set('services.adsense.client_id', 'ca-pub-test');
        $this->get(route('messages.show', $condolence))->assertDontSee('adsbygoogle');

        $sitemap = $this->get('/sitemap.xml')->assertOk();
        $sitemap->assertSee(route('messages.show', $m), false);
        $sitemap->assertDontSee('hidden-eid', false);
        $sitemap->assertSee('<lastmod>', false);

        $this->get('/feed.xml')->assertOk()->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')->assertSee('Eid Joy');
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee('Sitemap:');
    }

    public function test_views_are_tracked_for_people_not_bots(): void
    {
        $m = Message::create(['category' => 'eid', 'language' => 'en', 'body' => 'Eid Mubarak to all.', 'slug' => 'e1', 'status' => 'published']);
        $this->withHeader('User-Agent', 'Mozilla/5.0 (iPhone)')->get(route('messages.show', $m));
        $this->withHeader('User-Agent', 'Googlebot/2.1')->get(route('messages.show', $m));
        $this->assertSame(1, ContentView::where('content_type', 'message')->count());
    }

    public function test_admin_pages_render_and_editor_cannot_run_jobs_or_change_settings(): void
    {
        $this->fakeGemini($this->goodMessage());
        $draft = app(DraftGenerator::class)->generate('message', ['category' => 'good_morning', 'language' => 'en']);
        $admin = $this->admin();
        $bm = BirthdayMessage::create(['category_id' => Category::create(['department' => 'birthday_wishes', 'slug' => 'bw', 'name' => 'BW'])->id,
            'relationship' => 'friend', 'tone' => 'funny', 'language' => 'en', 'body' => 'Happy birthday!', 'slug' => 'hb', 'status' => 'draft']);

        foreach ([route('admin.automation.index'), route('admin.automation.settings'), route('admin.automation.calendar'),
            route('admin.ai-studio.index'), route('admin.ai-studio.show', $draft), route('admin.messages.index'),
            route('admin.messages.create'), route('admin.birthday-messages.edit', $bm), route('admin.dashboard')] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }

        // SEO box on an existing form saves + schedules
        $this->actingAs($admin)->put(route('admin.birthday-messages.update', $bm), [
            'category_id' => $bm->category_id, 'relationship' => 'friend', 'tone' => 'funny', 'language' => 'en', 'body' => 'Happy birthday!',
            'status' => 'published', 'meta_seo_title' => 'Funny Birthday Wish for a Friend', 'meta_publish_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'meta_faq' => "Is it free? || Yes, copy it.",
        ])->assertRedirect();
        $this->assertSame('in_review', $bm->fresh()->status);
        $this->assertSame('Funny Birthday Wish for a Friend', $bm->fresh()->meta->seo_title);
        $this->assertSame([['q' => 'Is it free?', 'a' => 'Yes, copy it.']], $bm->fresh()->meta->faq);

        $editor = $this->admin('editor');
        $this->actingAs($editor)->post(route('admin.automation.run', 'health_report'))->assertForbidden();
        $this->actingAs($editor)->put(route('admin.automation.settings.save'), [])->assertForbidden();

        $this->actingAs($admin)->post(route('admin.automation.run', 'health_report'))->assertRedirect();
        $this->assertSame('success', AutomationRun::where('job_key', 'health_report')->first()->status);
    }
}
