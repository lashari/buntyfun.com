<?php

namespace App\Services\Library;

use App\Content\QuizTopics;
use App\Models\Setting;
use App\Models\TriviaQuestion;
use Illuminate\Support\Facades\Http;

/**
 * Pulls questions from Open Trivia DB (API, verified questions only) and OpenTriviaQA
 * (GitHub text files) into the Question Bank as in_review. Both are CC BY-SA 4.0.
 * Plain HTTP calls inside the request/cron run; no queue worker or Node service.
 */
class TriviaImporter
{
    public function __construct(private readonly TriviaValidator $validator) {}

    /** @return array{saved:int, duplicates:int, rejected:int, reasons:array} */
    public function fromOpenTdb(string $topic, int $amount = 10): array
    {
        $categories = QuizTopics::ALL[$topic]['opentdb'] ?? [];
        if (! $categories) {
            throw new \InvalidArgumentException("No Open Trivia DB category for {$topic}");
        }
        $amount = max(1, min(50, $amount));
        $query = ['amount' => $amount, 'category' => $categories[0], 'type' => 'multiple', 'encode' => 'url3986'];
        if ($token = $this->openTdbToken()) {
            $query['token'] = $token;   // the token makes OpenTDB skip questions it already gave us
        }
        $res = Http::timeout(20)->acceptJson()->get('https://opentdb.com/api.php', $query)->throw()->json();
        $code = (int) ($res['response_code'] ?? -1);
        if (in_array($code, [3, 4], true)) {   // token missing / all questions used: reset and try next time
            Setting::put('opentdb_token', null);
        }
        if ($code === 5) {
            throw new \RuntimeException('Open Trivia DB rate limit (1 request per 5 seconds). Try again in a moment.');
        }

        $rows = [];
        foreach ($res['results'] ?? [] as $r) {
            $rows[] = [
                'prompt' => rawurldecode($r['question']), 'correct' => rawurldecode($r['correct_answer']),
                'wrong' => array_map('rawurldecode', $r['incorrect_answers']), 'difficulty' => $r['difficulty'] ?? 'medium',
                'ref' => 'opentdb:'.$categories[0],
            ];
        }

        return $this->save('opentdb', $topic, $rows);
    }

    /** Next $amount questions from an OpenTriviaQA category file, continuing where the last run stopped. */
    public function fromOpenTriviaQa(string $topic, int $amount = 10): array
    {
        $files = QuizTopics::ALL[$topic]['otqa'] ?? [];
        if (! $files) {
            throw new \InvalidArgumentException("No OpenTriviaQA file for {$topic}");
        }
        $file = $files[0];
        $text = Http::timeout(30)->get("https://raw.githubusercontent.com/uberspot/OpenTriviaQA/master/categories/{$file}")->throw()->body();
        $all = self::parseOpenTriviaQa($text);

        $key = 'otqa_offset_'.$file;
        $offset = (int) (Setting::get($key) ?? 0);
        $rows = [];
        $i = $offset;
        // Read on until $amount usable rows are collected (skipped ones do not count), max 10x.
        while (count($rows) < $amount && $i < count($all) && $i < $offset + $amount * 10) {
            $rows[] = $all[$i] + ['difficulty' => 'medium', 'ref' => "opentriviaqa:{$file}#".($i + 1)];
            $i++;
        }
        Setting::put($key, $i >= count($all) ? 0 : $i);

        return $this->save('opentriviaqa', $topic, $rows);
    }

    /** "#Q prompt / ^ answer / A..D options" blocks. Keeps only 4-option questions whose answer is one of them. */
    public static function parseOpenTriviaQa(string $text): array
    {
        $text = self::toUtf8($text);
        $out = [];
        foreach (preg_split('/\R\s*\R/u', $text) ?: [] as $block) {
            $lines = array_values(array_filter(array_map('trim', preg_split('/\R/u', trim($block)))));
            if (! $lines || ! str_starts_with($lines[0], '#Q')) {
                continue;
            }
            $prompt = trim(substr($lines[0], 2));
            $answer = null;
            $options = [];
            foreach (array_slice($lines, 1) as $l) {
                if (str_starts_with($l, '^')) {
                    $answer = trim(substr($l, 1));
                } elseif (preg_match('/^[A-D]\s+(.+)$/u', $l, $m)) {
                    $options[] = trim($m[1]);
                } elseif (! $answer && ! $options) {
                    $prompt .= ' '.$l;   // multi-line question
                }
            }
            if ($answer === null || count($options) !== 4 || ! in_array($answer, $options, true)) {
                continue;
            }
            if (self::looksGarbled($prompt.' '.implode(' ', $options))) {
                continue;   // text already double-encoded in the source file (e.g. "â€œ")
            }
            $out[] = ['prompt' => $prompt, 'correct' => $answer, 'wrong' => array_values(array_diff($options, [$answer]))];
        }

        return $out;
    }

    /** Validate, de-duplicate and store. Public so the tests and the admin "paste" path share it. */
    /**
     * OpenTriviaQA files are UTF-8 with a few Windows-1252 lines (e.g. "Café" as one byte).
     * With /u, preg_split returns false on such bytes, so fix only the invalid lines.
     */
    public static function toUtf8(string $text): string
    {
        if (mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }
        $lines = explode("\n", $text);
        foreach ($lines as $i => $line) {
            if (! mb_check_encoding($line, 'UTF-8')) {
                $lines[$i] = mb_convert_encoding($line, 'UTF-8', 'Windows-1252');
            }
        }

        return implode("\n", $lines);
    }

    /** Mojibake such as "â€œ", "Ã©", "Ï†" means the line was double-encoded and cannot be trusted. */
    public static function looksGarbled(string $s): bool
    {
        return (bool) preg_match('/â€|Ã[\x{80}-\x{BF}\x{A0}-\x{FF}]|[ÎÏ][\x{80}-\x{BF}€‚ƒ„…†‡ˆ‰Š‹ŒŽ‘’“”•–—˜™š›œžŸ]/u', $s);
    }

    public function save(string $source, string $topic, array $rows): array
    {
        $stats = ['saved' => 0, 'duplicates' => 0, 'rejected' => 0, 'reasons' => []];
        foreach ($rows as $r) {
            $prompt = TriviaValidator::clean($r['prompt']);
            $correct = TriviaValidator::clean($r['correct']);
            $wrong = array_map([TriviaValidator::class, 'clean'], $r['wrong']);
            [$reject, $flags] = $this->validator->check($prompt, $correct, $wrong);
            if ($reject) {
                $stats['rejected']++;
                $stats['reasons'][] = mb_substr($prompt, 0, 60).'… — '.$reject;
                continue;
            }
            $hash = TriviaQuestion::hashFor($prompt, $correct);
            if (TriviaQuestion::where('hash', $hash)->exists()) {
                $stats['duplicates']++;
                continue;
            }
            TriviaQuestion::create([
                'source' => $source, 'source_ref' => $r['ref'] ?? null, 'hash' => $hash, 'topic' => $topic,
                'difficulty' => in_array($r['difficulty'] ?? '', ['easy', 'medium', 'hard'], true) ? $r['difficulty'] : 'medium',
                'prompt' => $prompt, 'correct_answer' => $correct, 'wrong_answers' => $wrong,
                'explanation' => $r['explanation'] ?? null, 'status' => 'in_review', 'flags' => $flags ?: null,
            ]);
            $stats['saved']++;
        }

        return $stats;
    }

    private function openTdbToken(): ?string
    {
        $token = Setting::get('opentdb_token');
        if ($token) {
            return $token;
        }
        try {
            $token = Http::timeout(10)->get('https://opentdb.com/api_token.php', ['command' => 'request'])->json('token');
        } catch (\Throwable) {
            return null;
        }
        if ($token) {
            Setting::put('opentdb_token', $token);
        }

        return $token ?: null;
    }
}
