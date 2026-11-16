<?php

namespace Tests\Feature;

use App\Models\AiApiKey;
use App\Models\AiTool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiKeyPoolTest extends TestCase
{
    use RefreshDatabase;

    private function key(string $label, string $secret, int $priority = 10): AiApiKey
    {
        return AiApiKey::create(['provider' => 'gemini', 'label' => $label, 'api_key' => $secret, 'last4' => substr($secret, -4), 'priority' => $priority]);
    }

    private function tool(): AiTool
    {
        return AiTool::create(['slug' => 'joke-generator', 'title' => 'Jokes', 'description' => 'd', 'prompt_template' => 'Joke about {input}', 'status' => 'published']);
    }

    private function ok(string $text = 'A joke'): array
    {
        return ['candidates' => [['content' => ['parts' => [['text' => $text]]]]]];
    }

    public function test_quota_hit_rotates_to_next_key_and_rests_the_first(): void
    {
        Config::set('ai.gemini.key', null);
        $a = $this->key('A', 'AIzaFIRST-key-0000000001', 1);
        $b = $this->key('B', 'AIzaSECOND-key-000000002', 2);
        Http::fake(function (Request $r) {
            return $r->header('x-goog-api-key')[0] === 'AIzaFIRST-key-0000000001'
                ? Http::response(['error' => 'quota'], 429)
                : Http::response($this->ok('from B'));
        });

        $this->postJson(route('ai-tools.generate', $this->tool()), ['input' => 'cats'])->assertOk()->assertJson(['output' => 'from B']);

        $this->assertTrue($a->fresh()->isCoolingDown());
        $this->assertStringContainsString('429', $a->fresh()->last_error);
        $this->assertSame(1, $b->fresh()->use_count);
        // The key never travels in the URL.
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'key='));
    }

    public function test_requests_spread_across_keys_least_recently_used_first(): void
    {
        $this->key('A', 'AIzaFIRST-key-0000000001');
        $this->key('B', 'AIzaSECOND-key-000000002');
        $seen = [];
        Http::fake(function (Request $r) use (&$seen) {
            $seen[] = $r->header('x-goog-api-key')[0];

            return Http::response($this->ok());
        });
        $tool = $this->tool();
        $this->postJson(route('ai-tools.generate', $tool), ['input' => 'a'])->assertOk();
        $this->travel(1)->seconds();
        $this->postJson(route('ai-tools.generate', $tool), ['input' => 'b'])->assertOk();

        $this->assertCount(2, array_unique($seen));
    }

    public function test_env_key_is_last_fallback_and_no_keys_fails_cleanly(): void
    {
        Config::set('ai.gemini.key', 'ENV-KEY-123456789012345');
        $this->key('Bad', 'AIzaBAD-key-00000000001');
        Http::fake(fn (Request $r) => $r->header('x-goog-api-key')[0] === 'ENV-KEY-123456789012345'
            ? Http::response($this->ok('from env')) : Http::response([], 403));

        $this->postJson(route('ai-tools.generate', $this->tool()), ['input' => 'x'])->assertOk()->assertJson(['output' => 'from env']);
        $this->assertStringContainsString('403', AiApiKey::first()->last_error);

        Config::set('ai.gemini.key', null);
        AiApiKey::query()->update(['cooldown_until' => null]);
        $this->postJson(route('ai-tools.generate', AiTool::first()), ['input' => 'y'])->assertStatus(502);
    }

    public function test_super_admin_adds_key_encrypted_and_never_sees_it_again(): void
    {
        $super = User::create(['name' => 'S', 'email' => 's@t.com', 'password' => 'p', 'role' => 'super_admin']);
        $admin = User::create(['name' => 'A', 'email' => 'a@t.com', 'password' => 'p', 'role' => 'admin']);
        $secret = 'AIzaSyPLAINTEXT-secret-WXYZ';

        $this->actingAs($admin)->post(route('admin.ai-keys.store'), ['provider' => 'gemini', 'label' => 'K', 'api_key' => $secret])->assertForbidden();

        $this->actingAs($super)->post(route('admin.ai-keys.store'), ['provider' => 'gemini', 'label' => 'Key 2', 'api_key' => $secret])->assertRedirect();
        $raw = DB::table('ai_api_keys')->value('api_key');
        $this->assertStringNotContainsString('PLAINTEXT', $raw);
        $this->assertSame($secret, AiApiKey::first()->secret());

        $this->actingAs($super)->post(route('admin.ai-keys.store'), ['provider' => 'gemini', 'label' => 'Again', 'api_key' => $secret])->assertSessionHasErrors();
        $this->assertSame(1, AiApiKey::count());

        $page = $this->actingAs($super)->get(route('admin.automation.settings'))->assertOk();
        $page->assertSee('••••WXYZ');
        $page->assertDontSee('PLAINTEXT');

        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->ok('OK'))]);
        $this->actingAs($super)->post(route('admin.ai-keys.test', AiApiKey::first()))->assertSessionHas('status');

        $this->actingAs($super)->post(route('admin.ai-keys.toggle', AiApiKey::first()));
        $this->assertFalse(AiApiKey::first()->enabled);
        $this->actingAs($super)->delete(route('admin.ai-keys.destroy', AiApiKey::first()))->assertRedirect();
        $this->assertSame(0, AiApiKey::count());
    }
}
