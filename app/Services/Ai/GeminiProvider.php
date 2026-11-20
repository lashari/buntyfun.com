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
        return $this->call($prompt, 300, 0.9, false)->text;
    }

    public function generateDetailed(string $prompt, int $maxTokens = 1500, float $temperature = 0.8): AiResult
    {
        return $this->call($prompt, $maxTokens, $temperature, true);
    }

    private function call(string $prompt, int $maxTokens, float $temperature, bool $json): AiResult
    {
        if (! $this->apiKey) {
            throw AiGenerationException::notConfigured('Gemini');
        }

        try {
            $config = ['maxOutputTokens' => $maxTokens, 'temperature' => $temperature];
            if ($json) {
                $config['responseMimeType'] = 'application/json';
            }

            // Key in a header, not the URL, so it can never show up in a logged URL.
            $response = Http::timeout($json ? 60 : 15)
                ->withHeaders(['x-goog-api-key' => $this->apiKey])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => $config,
                    // Public, general-audience site: block harmful output rather than rely on the prompt alone.
                    // A blocked reply has no text and surfaces as emptyResponse() → friendly error.
                    'safetySettings' => [
                        ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_LOW_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_LOW_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ],
                ]);
        } catch (Throwable $e) {
            Log::error('Gemini request failed', ['error' => $e->getMessage()]);
            throw AiGenerationException::requestFailed();
        }

        if ($response->failed()) {
            Log::error('Gemini returned an error status', ['status' => $response->status()]);
            throw AiGenerationException::requestFailed($response->status());
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw AiGenerationException::emptyResponse();
        }

        return new AiResult(
            trim($text),
            $response->json('usageMetadata.promptTokenCount'),
            $response->json('usageMetadata.candidatesTokenCount'),
        );
    }
}
