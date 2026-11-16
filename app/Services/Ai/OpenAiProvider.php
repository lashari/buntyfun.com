<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenAiProvider implements AiProviderContract
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $model = 'gpt-4o-mini',
    ) {}

    public function generate(string $prompt): string
    {
        return $this->call($prompt, 300, 0.9, false)->text;
    }

    public function generateDetailed(string $prompt, int $maxTokens = 1500, float $temperature = 0.8): AiResult
    {
        return $this->call($prompt, $maxTokens, $temperature, true);
    }

    private function call(string $prompt, int $maxTokens, float $temperature, bool $json): AiResult
    {
        if (! $this->apiKey) {
            throw AiGenerationException::notConfigured('OpenAI');
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($json ? 60 : 15)
                ->post('https://api.openai.com/v1/chat/completions', array_filter([
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => $maxTokens,
                    'temperature' => $temperature,
                    'response_format' => $json ? ['type' => 'json_object'] : null,
                ]));
        } catch (Throwable $e) {
            Log::error('OpenAI request failed', ['error' => $e->getMessage()]);
            throw AiGenerationException::requestFailed();
        }

        if ($response->failed()) {
            Log::error('OpenAI returned an error status', ['status' => $response->status()]);
            throw AiGenerationException::requestFailed($response->status());
        }

        $text = $response->json('choices.0.message.content');

        if (! is_string($text) || trim($text) === '') {
            throw AiGenerationException::emptyResponse();
        }

        return new AiResult(
            trim($text),
            $response->json('usage.prompt_tokens'),
            $response->json('usage.completion_tokens'),
        );
    }
}
