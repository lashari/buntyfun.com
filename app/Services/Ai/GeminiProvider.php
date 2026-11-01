<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiProvider implements AiProviderContract
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $model = 'gemini-flash-lite-latest',
    ) {}

    public function generate(string $prompt): string
    {
        if (! $this->apiKey) {
            throw AiGenerationException::notConfigured('Gemini');
        }

        try {
            $response = Http::timeout(15)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => 300,
                        'temperature' => 0.9,
                    ],
                ]);
        } catch (Throwable $e) {
            Log::error('Gemini request failed', ['error' => $e->getMessage()]);
            throw AiGenerationException::requestFailed();
        }

        if ($response->failed()) {
            Log::error('Gemini returned an error status', ['status' => $response->status()]);
            throw AiGenerationException::requestFailed();
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw AiGenerationException::emptyResponse();
        }

        return trim($text);
    }
}
