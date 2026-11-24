<?php

namespace App\Services\Library;

/**
 * Checks one imported question before it reaches the Question Bank.
 * reject = never saved (broken or unsuitable for a family site);
 * flags  = saved in review with a warning the admin should read.
 */
class TriviaValidator
{
    /** Topics BuntyFun does not run (family site, Muslim-majority audience, AdSense rules). */
    private const BLOCK = ['alcohol', 'beer', 'wine', 'vodka', 'whisky', 'whiskey', 'cocktail', 'brewery', 'marijuana', 'cannabis',
        'cocaine', 'heroin', 'casino', 'gambling', 'poker', 'porn', 'sex', 'nude', 'playboy', 'pork', 'bacon', 'ham ', 'stripper', 'drunk'];

    /** Not wrong in itself, but needs a human look before publishing. */
    private const SENSITIVE = ['israel', 'palestin', 'kashmir', 'gaza', 'jerusalem', 'prophet', 'muhammad', 'allah', 'quran', 'bible',
        'jesus', 'god ', 'religion', 'church', 'temple', 'hindu', 'nazi', 'hitler', 'terror', 'war crime', 'genocide', 'slave', 'india', 'taiwan'];

    /** Answers whose meaning depends on the other options or on a picture we do not have. */
    private const CONTEXT = ['all of these', 'all of the above', 'none of these', 'none of the above', 'both a and b'];

    public static function clean(?string $s): string
    {
        $s = html_entity_decode((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $s = preg_replace('/[\x{200B}-\x{200F}\x{2028}\x{202F}]/u', '', $s);

        return trim(preg_replace('/\s+/u', ' ', $s));
    }

    /** @return array{0: ?string, 1: array} [reject reason or null, flags] */
    public function check(string $prompt, string $correct, array $wrong): array
    {
        $flags = [];
        $all = array_merge([$correct], $wrong);
        $text = mb_strtolower($prompt.' '.implode(' ', $all)).' ';

        if (mb_strlen($prompt) < 12 || mb_strlen($prompt) > 300) {
            return ['Question too short or too long', []];
        }
        if (count($wrong) !== 3 || in_array('', $all, true)) {
            return ['Needs exactly one right and three wrong answers', []];
        }
        if (count(array_unique(array_map('mb_strtolower', $all))) !== 4) {
            return ['Two answers are the same', []];
        }
        foreach ($all as $a) {
            if (mb_strlen($a) > 120) {
                return ['An answer is longer than 120 characters', []];
            }
            if (in_array(mb_strtolower($a), self::CONTEXT, true)) {
                return ['Uses "all/none of these", which breaks when answers are shuffled', []];
            }
        }
        if (preg_match('/(following (sentence|picture|image|quote|exchange|equation)|pictured|shown (here|below)|this equation|:\s*$)/i', $prompt)) {
            return ['Refers to a picture or text that is not part of the question', []];
        }
        foreach (self::BLOCK as $w) {
            if (str_contains($text, $w)) {
                return ["Unsuitable topic ({$w})", []];
            }
        }
        foreach (self::SENSITIVE as $w) {
            if (str_contains($text, $w)) {
                $flags[] = 'Sensitive topic ('.trim($w).'): check wording and facts';
            }
        }
        if (preg_match('/\b(19|20)\d\d\b/', $prompt) || preg_match('/\b(current|currently|now|latest|today|this year|recent)\b/i', $prompt)) {
            $flags[] = 'Mentions a date or "current": check it is still true';
        }
        if (preg_match('/\b(most|record|largest|biggest|highest|tallest|best)\b/i', $prompt)) {
            $flags[] = 'Record or superlative: confirm it has not changed';
        }
        if (preg_match('/\b(NFL|NBA|MLB|NHL|Super Bowl|baseball|American football)\b/i', $prompt.' '.$correct)) {
            $flags[] = 'US-only sport: may not interest our readers';
        }

        return [null, $flags];
    }
}
