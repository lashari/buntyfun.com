<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiApiKey;
use App\Models\AuditLog;
use App\Services\Ai\AiGenerationException;
use App\Services\Ai\GeminiProvider;
use App\Services\Ai\OpenAiProvider;
use Illuminate\Http\Request;

/**
 * AI API keys for AI Fun tools + AI Studio. Super admins only. A saved key is never
 * shown again: only its label and last 4 characters.
 */
class AiKeyAdminController extends Controller
{
    public function store(Request $request)
    {
        $this->authorizeKeys($request);
        $data = $request->validate([
            'provider' => ['required', 'in:gemini,openai'],
            'label' => ['required', 'string', 'max:60'],
            'api_key' => ['required', 'string', 'min:20', 'max:300', 'regex:/^\S+$/'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:99'],
        ], ['api_key.regex' => 'The key must not contain spaces.']);

        $key = trim($data['api_key']);
        $exists = AiApiKey::where('provider', $data['provider'])->get()->contains(fn ($k) => $k->secret() === $key);
        if ($exists) {
            return back()->withErrors('That key is already saved.');
        }

        $row = AiApiKey::create([
            'provider' => $data['provider'],
            'label' => $data['label'],
            'api_key' => $key,
            'last4' => substr($key, -4),
            'priority' => $data['priority'] ?? 10,
            'created_by' => $request->user()->id,
        ]);
        AuditLog::record($request->user(), 'ai_key_added', 'ai_api_key', $row->id);

        return back()->with('status', "Key \"{$row->label}\" ({$row->masked()}) saved. Use Test to check it.");
    }

    public function toggle(Request $request, AiApiKey $key)
    {
        $this->authorizeKeys($request);
        $key->update(['enabled' => ! $key->enabled, 'cooldown_until' => null, 'fail_count' => 0]);
        AuditLog::record($request->user(), $key->enabled ? 'ai_key_enabled' : 'ai_key_disabled', 'ai_api_key', $key->id);

        return back()->with('status', "Key \"{$key->label}\" ".($key->enabled ? 'turned on.' : 'turned off.'));
    }

    /** One tiny real request with this key only. */
    public function test(Request $request, AiApiKey $key)
    {
        $this->authorizeKeys($request);
        $secret = $key->secret();
        if (! $secret) {
            return back()->withErrors("Key \"{$key->label}\" can't be read (APP_KEY changed?). Delete and add it again.");
        }

        $provider = $key->provider === 'openai' ? new OpenAiProvider($secret) : new GeminiProvider($secret);
        try {
            $provider->generate('Reply with the single word OK.');
            $key->update(['last_error' => null, 'fail_count' => 0, 'cooldown_until' => null]);

            return back()->with('status', "Key \"{$key->label}\" works ✓");
        } catch (AiGenerationException $e) {
            $why = match (true) {
                $e->status === 429 => 'quota or rate limit reached (429)',
                in_array($e->status, [400, 401, 403], true) => "rejected by {$key->provider} ({$e->status}) — wrong, revoked or API not enabled",
                default => $e->status ? "provider error {$e->status}" : 'no response (network)',
            };
            $key->update(['last_error' => mb_substr($why, 0, 120)]);

            return back()->withErrors("Key \"{$key->label}\" failed: {$why}.");
        }
    }

    public function destroy(Request $request, AiApiKey $key)
    {
        $this->authorizeKeys($request);
        AuditLog::record($request->user(), 'ai_key_deleted', 'ai_api_key', $key->id);
        $label = $key->label;
        $key->delete();

        return back()->with('status', "Key \"{$label}\" deleted.");
    }

    private function authorizeKeys(Request $request): void
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
    }
}
