<?php

namespace App\Services\Ai;

interface AiProviderContract
{
    /**
     * Short visitor-facing generation (AI Fun Studio).
     *
     * @throws AiGenerationException
     */
    public function generate(string $prompt): string;

    /**
     * Longer admin/automation generation: asks for JSON output and reports token usage
     * so the automation dashboard can show real spend.
     *
     * @throws AiGenerationException
     */
    public function generateDetailed(string $prompt, int $maxTokens = 1500, float $temperature = 0.8): AiResult;
}
