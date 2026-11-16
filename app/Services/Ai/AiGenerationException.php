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
    /** HTTP status from the provider (0 = network/no response), used to rotate keys. */
    public int $status = 0;

    public static function notConfigured(string $provider): self
    {
        return new self("The {$provider} AI provider has no API key configured.");
    }

    public static function requestFailed(int $status = 0): self
    {
        $e = new self('The AI provider request failed.');
        $e->status = $status;

        return $e;
    }

    /** Quota / rate limit / bad or revoked key: worth trying the next key. */
    public function isKeyProblem(): bool
    {
        return in_array($this->status, [400, 401, 403, 429], true);
    }

    public static function emptyResponse(): self
    {
        return new self('The AI provider returned an empty response.');
    }
}
