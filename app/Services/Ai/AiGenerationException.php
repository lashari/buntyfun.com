<?php

namespace App\Services\Ai;

use Exception;

/**
 * Thrown for anything a provider call can go wrong — missing key, network failure,
 * malformed response, empty output. The message is always safe to show a user;
 * it never carries a provider's raw error body or any part of an API key.
 */
class AiGenerationException extends Exception
{
    public static function notConfigured(string $provider): self
    {
        return new self("The {$provider} AI provider has no API key configured.");
    }

    public static function requestFailed(): self
    {
        return new self('The AI provider request failed.');
    }

    public static function emptyResponse(): self
    {
        return new self('The AI provider returned an empty response.');
    }
}
