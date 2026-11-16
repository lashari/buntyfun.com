<?php

namespace App\Services\Ai;

final class AiResult
{
    public function __construct(
        public readonly string $text,
        public readonly ?int $inputTokens = null,
        public readonly ?int $outputTokens = null,
    ) {}
}
