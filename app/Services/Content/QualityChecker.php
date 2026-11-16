<?php

namespace App\Services\Content;

use App\Services\Content\Templates\ContentTemplate;
use Illuminate\Support\Str;

/**
 * Runs before any draft can be approved. Blocking failures stop approval; warnings
 * lower the score. Every check is a plain rule a reviewer can read, no AI judging AI.
 */
class QualityChecker
{
    private const AI_PHRASES = ['as an ai', 'language model', 'i cannot', "i can't", 'here is a', 'here\'s a', 'certainly!', 'sure!', '[name]', '{name}', 'lorem ipsum'];

    private const STOP = ['the', 'a', 'an', 'and', 'or', 'to', 'of', 'in', 'on', 'for', 'you', 'your', 'is', 'are', 'be', 'with', 'my', 'me', 'i', 'it', 'that', 'this', 'all', 'so', 'as', 'at', 'have', 'may', 'we', 'our', 'will'];

    /** @return array{checks: array, score: int} */
    public function run(ContentTemplate $template, array $payload): array
    {
        $text = $template->comparableText($payload);
        $main = (string) ($payload['body'] ?? $payload['meaning'] ?? $text);
        $words = $this->words($template->type() === 'name' ? $text : $main);
        [$min, $max] = $template->wordRange();
        $checks = [];

        $checks[] = $this->check('not_empty', 'Main text present', trim($text) !== '', true);
        $checks[] = $this->check('length', "Length {$min}-{$max} words", count($words) >= $min && count($words) <= $max, true, count($words).' words');

        $dup = $this->maxSimilarity($text, $template->existingTexts()->all(), $template->type() === 'name');
        $checks[] = $this->check('duplicate', 'Not a duplicate of existing content', $dup < 0.5, true, 'closest match '.round($dup * 100).'%');
        $checks[] = $this->check('near_duplicate', 'Clearly different from existing content', $dup < 0.35, false, $dup >= 0.35 ? 'similar to an existing item' : null);

        $lower = Str::lower($main.' '.($payload['title'] ?? ''));
        $checks[] = $this->check('no_links', 'No links, emails or phone numbers', ! preg_match('~https?://|www\.|@[a-z0-9-]+\.[a-z]|\+?\d[\d\s-]{8,}\d~i', $lower), true);
        $hit = collect(self::AI_PHRASES)->first(fn ($p) => str_contains($lower, $p));
        $checks[] = $this->check('no_ai_phrases', 'No AI filler or placeholders', $hit === null, true, $hit ? "found \"{$hit}\"" : null);

        if (count($words) >= 25) {
            $freq = collect($words)->reject(fn ($w) => in_array($w, self::STOP, true) || mb_strlen($w) < 3)->countBy()->sortDesc();
            $top = $freq->keys()->first();
            $ratio = $top ? $freq->first() / count($words) : 0;
            $checks[] = $this->check('no_stuffing', 'No keyword stuffing', $ratio <= 0.12, $ratio > 0.2, $top ? "\"{$top}\" ".round($ratio * 100).'%' : null);
        }

        if ($template->language($payload) === 'ur') {
            $letters = preg_match_all('/\p{L}/u', $main);
            $arabic = preg_match_all('/\p{Arabic}/u', $main);
            $share = $letters ? $arabic / $letters : 0;
            $checks[] = $this->check('urdu_script', 'Written in Urdu script', $share >= 0.7, true, round($share * 100).'% Urdu letters');
        }

        $t = mb_strlen($payload['seo_title'] ?? '');
        $d = mb_strlen($payload['seo_description'] ?? '');
        $checks[] = $this->check('seo_title', 'SEO title 20-60 characters', $t >= 20 && $t <= 60, false, "{$t} chars");
        $checks[] = $this->check('seo_description', 'SEO description 70-160 characters', $d >= 70 && $d <= 160, false, "{$d} chars");

        $checks = array_merge($checks, $template->extraChecks($payload));

        $score = 100;
        foreach ($checks as $c) {
            if (! $c['ok']) {
                $score -= $c['blocking'] ? 25 : 8;
            }
        }

        return ['checks' => $checks, 'score' => max(0, $score)];
    }

    /** Highest Jaccard similarity of word 2-shingles (exact match for names). */
    public function maxSimilarity(string $text, array $existing, bool $exact = false): float
    {
        if ($exact) {
            return in_array(Str::lower(trim($text)), array_map(fn ($e) => Str::lower(trim((string) $e)), $existing), true) ? 1.0 : 0.0;
        }

        $a = $this->shingles($text);
        if (! $a) {
            return 0.0;
        }

        $best = 0.0;
        foreach ($existing as $e) {
            $b = $this->shingles((string) $e);
            if (! $b) continue;
            $inter = count(array_intersect_key($a, $b));
            $union = count($a + $b);
            $best = max($best, $union ? $inter / $union : 0);
        }

        return $best;
    }

    private function shingles(string $s): array
    {
        $w = $this->words($s);
        $out = [];
        for ($i = 0; $i < count($w) - 1; $i++) {
            $out[$w[$i].' '.$w[$i + 1]] = true;
        }

        return $out;
    }

    private function words(string $s): array
    {
        preg_match_all('/[\p{L}\p{N}\']+/u', Str::lower($s), $m);

        return $m[0];
    }

    private function check(string $key, string $label, bool $ok, bool $blocking, ?string $note = null): array
    {
        return compact('key', 'label', 'ok', 'blocking', 'note');
    }
}
