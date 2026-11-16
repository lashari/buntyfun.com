<?php

namespace App\Services\Ai;

use App\Models\AiApiKey;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Tries the admin's keys for the active provider (least recently used first, so load is
 * spread), then the .env key as the last fallback. A key that hits a quota (429) rests
 * 10 minutes; a rejected key (400/401/403) rests 6 hours. Visitors never see which key
 * was used or why one failed.
 */
class RotatingAiProvider implements AiProviderContract
{
    /** @param Closure(string $key): AiProviderContract $make */
    public function __construct(
        private readonly string $provider,
        private readonly Closure $make,
        private readonly ?string $envKey,
    ) {}

    public function generate(string $prompt): string
    {
        return $this->attempt(fn (AiProviderContract $p) => $p->generate($prompt));
    }

    public function generateDetailed(string $prompt, int $maxTokens = 1500, float $temperature = 0.8): AiResult
    {
        return $this->attempt(fn (AiProviderContract $p) => $p->generateDetailed($prompt, $maxTokens, $temperature));
    }

    private function attempt(Closure $call): mixed
    {
        $last = null;

        foreach ($this->keys() as $key) {
            $secret = $key->secret();
            if (! $secret) {
                $key->update(['last_error' => 'cannot decrypt (APP_KEY changed?) — re-add this key']);
                continue;
            }

            try {
                $result = $call(($this->make)($secret));
                $key->forceFill(['use_count' => $key->use_count + 1, 'last_used_at' => now(), 'fail_count' => 0, 'last_error' => null, 'cooldown_until' => null])->save();

                return $result;
            } catch (AiGenerationException $e) {
                $last = $e;
                $this->markFailed($key, $e);
                if (! $e->isKeyProblem() && $e->status !== 0) {
                    throw $e; // a provider-side 5xx etc. would fail on every key too
                }
            }
        }

        if ($this->envKey) {
            return $call(($this->make)($this->envKey));
        }

        throw $last ?? AiGenerationException::notConfigured(ucfirst($this->provider));
    }

    /** @return iterable<AiApiKey> */
    private function keys(): iterable
    {
        try {
            if (! Schema::hasTable('ai_api_keys')) {
                return [];
            }

            return AiApiKey::where('provider', $this->provider)->where('enabled', true)
                ->where(fn ($q) => $q->whereNull('cooldown_until')->orWhere('cooldown_until', '<', now()))
                ->orderBy('priority')->orderByRaw('last_used_at is not null')->orderBy('last_used_at')
                ->get();
        } catch (Throwable $e) {
            Log::error('AI key pool unavailable', ['error' => $e->getMessage()]);

            return [];
        }
    }

    private function markFailed(AiApiKey $key, AiGenerationException $e): void
    {
        [$rest, $why] = match (true) {
            $e->status === 429 => [10, 'quota / rate limit (429)'],
            in_array($e->status, [400, 401, 403], true) => [360, "rejected ({$e->status}) — check the key"],
            default => [2, $e->status ? "error {$e->status}" : 'network error'],
        };

        $key->forceFill([
            'fail_count' => $key->fail_count + 1,
            'last_error' => $why,
            'cooldown_until' => now()->addMinutes($rest),
        ])->save();
    }
}
