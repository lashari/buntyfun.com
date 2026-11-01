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
        if (! $this->apiKey) {
            throw AiGenerationException::notConfigured('OpenAI');
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => 300,
                    'temperature' => 0.9,
                ]);
        } catch (Throwable $e) {
            Log::error('OpenAI request failed', ['error' => $e->getMessage()]);
            throw AiGenerationException::requestFailed();
        }

        if ($response->failed()) {
            Log::error('OpenAI returned an error status', ['status' => $response->status()]);
            throw AiGenerationException::requestFailed();
        }

        $text = $response->json('choices.0.message.content');

        if (! is_string($text) || trim($text) === '') {
            throw AiGenerationException::emptyResponse();
        }

        return trim($text);
    }
}
