<?php

namespace App\Services\Ai;

interface AiProviderContract
{
    /**
     * @throws AiGenerationException
     */
    public function generate(string $prompt): string;
}
