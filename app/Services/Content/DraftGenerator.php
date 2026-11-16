<?php

namespace App\Services\Content;

use App\Models\AiDraft;
use App\Models\AutomationRun;
use App\Models\Setting;
use App\Services\Ai\AiGenerationException;
use App\Services\Ai\AiProviderContract;
use Illuminate\Support\Facades\Log;
use Throwable;

class DraftGenerator
{
    public function __construct(
        private readonly AiProviderContract $ai,
        private readonly QualityChecker $checker,
    ) {}

    /** Generates one draft. Never throws: a failure is saved as a 'failed' draft with the reason. */
    public function generate(string $type, array $params, ?AutomationRun $run = null): AiDraft
    {
        $template = TemplateRegistry::get($type);
        $prompt = $template->prompt($params);
        $draft = AiDraft::create([
            'content_type' => $type,
            'params' => collect($params)->except('avoid')->all(),
            'prompt' => $prompt,
            'status' => 'generated',
            'automation_run_id' => $run?->id,
        ]);

        try {
            $result = $this->ai->generateDetailed($prompt);
        } catch (AiGenerationException $e) {
            return tap($draft)->update(['status' => 'failed', 'error' => $e->getMessage()]);
        } catch (Throwable $e) {
            Log::error('Draft generation crashed', ['draft' => $draft->id, 'error' => $e->getMessage()]);

            return tap($draft)->update(['status' => 'failed', 'error' => 'Unexpected error, see laravel.log.']);
        }

        $cost = self::cost($result->inputTokens, $result->outputTokens);
        $draft->fill([
            'raw_output' => $result->text,
            'input_tokens' => $result->inputTokens,
            'output_tokens' => $result->outputTokens,
            'cost_usd' => $cost,
        ]);

        $json = self::decodeJson($result->text);
        if ($json === null) {
            $draft->fill(['status' => 'failed', 'error' => 'AI did not return valid JSON.'])->save();

            return $draft;
        }

        $payload = $template->normalize($json, $params);
        $quality = $this->checker->run($template, $payload);
        $draft->fill(['payload' => $payload, 'checks' => $quality['checks'], 'quality_score' => $quality['score']])->save();

        return $draft;
    }

    /** Re-run checks after an admin edits the payload. */
    public function recheck(AiDraft $draft, array $payload): AiDraft
    {
        $quality = $this->checker->run(TemplateRegistry::get($draft->content_type), $payload);
        $draft->update(['payload' => $payload, 'checks' => $quality['checks'], 'quality_score' => $quality['score']]);

        return $draft;
    }

    public static function cost(?int $in, ?int $out): ?float
    {
        if ($in === null && $out === null) {
            return null;
        }

        return round(((int) $in * (float) Setting::get('ai_price_input_per_m') + (int) $out * (float) Setting::get('ai_price_output_per_m')) / 1_000_000, 6);
    }

    public static function decodeJson(string $text): ?array
    {
        $text = trim(preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($text)));
        $data = json_decode($text, true);
        if (! is_array($data) && preg_match('/\{.*\}/s', $text, $m)) {
            $data = json_decode($m[0], true);
        }
        if (is_array($data) && array_is_list($data) && isset($data[0]) && is_array($data[0])) {
            $data = $data[0];
        }

        return is_array($data) ? $data : null;
    }

    public static function monthSpend(): float
    {
        return (float) AiDraft::where('created_at', '>=', now()->startOfMonth())->sum('cost_usd');
    }
}
