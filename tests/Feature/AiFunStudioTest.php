<?php

namespace Tests\Feature;

use App\Models\AiTool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiFunStudioTest extends TestCase
{
    use RefreshDatabase;

    public function test_generation_fails_cleanly_when_no_api_key_is_configured(): void
    {
        Config::set('ai.gemini.key', null);
        $tool = $this->makeTool();

        $response = $this->postJson(route('ai-tools.generate', $tool), ['input' => 'cats']);

        $response->assertStatus(502);
        $response->assertJsonStructure(['error']);
        $this->assertStringNotContainsString('key', strtolower($response->json('error')));
    }

    public function test_successful_generation_with_a_mocked_provider_response(): void
    {
        Config::set('ai.gemini.key', 'test-key');
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'Why did the cat sit on the computer? To keep an eye on the mouse.']]]],
                ],
            ], 200),
        ]);

        $tool = $this->makeTool();

        $response = $this->postJson(route('ai-tools.generate', $tool), ['input' => 'cats']);

        $response->assertOk();
        $response->assertJson(['output' => 'Why did the cat sit on the computer? To keep an eye on the mouse.']);
        $this->assertDatabaseCount('ai_usages', 1);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com')
                && str_contains(json_encode($request->data()), 'cats');
        });
    }

    public function test_daily_free_limit_is_enforced(): void
    {
        Config::set('ai.gemini.key', 'test-key');
        Config::set('ai.daily_free_limit', 2);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'A joke.']]]]],
            ], 200),
        ]);

        $tool = $this->makeTool();

        $this->postJson(route('ai-tools.generate', $tool), ['input' => 'a'])->assertOk();
        $this->postJson(route('ai-tools.generate', $tool), ['input' => 'b'])->assertOk();
        $response = $this->postJson(route('ai-tools.generate', $tool), ['input' => 'c']);

        $response->assertStatus(429);
        $this->assertDatabaseCount('ai_usages', 2);
    }

    public function test_input_is_required_and_length_limited(): void
    {
        $tool = $this->makeTool();

        $this->postJson(route('ai-tools.generate', $tool), ['input' => ''])->assertStatus(422);
        $this->postJson(route('ai-tools.generate', $tool), ['input' => str_repeat('a', 301)])->assertStatus(422);
    }

    public function test_unpublished_tool_returns_404(): void
    {
        $tool = AiTool::create([
            'slug' => 'draft-tool', 'title' => 'Draft', 'description' => 'd',
            'prompt_template' => '{input}', 'status' => 'draft',
        ]);

        $this->get(route('ai-tools.show', $tool))->assertStatus(404);
    }

    private function makeTool(): AiTool
    {
        return AiTool::create([
            'slug' => 'joke-generator', 'title' => 'Joke Generator', 'description' => 'd',
            'prompt_template' => 'Tell a joke about {input}', 'status' => 'published',
        ]);
    }
}
