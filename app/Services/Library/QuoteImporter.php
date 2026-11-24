<?php

namespace App\Services\Library;

use App\Content\QuoteTopics;
use App\Models\Quote;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

/**
 * QuoteSlate (github.com/musheer360/QuoteSlate, MIT) → quotes table, IN REVIEW.
 * Each run takes the next batch, continuing where the previous one stopped.
 */
class QuoteImporter
{
    public const URL = 'https://raw.githubusercontent.com/musheer360/QuoteSlate/main/data/quotes.json';

    /** Quotes often credited to these people without a reliable source: admin should double-check. */
    private const DISPUTED = ['buddha', 'albert einstein', 'confucius', 'mark twain', 'abraham lincoln', 'mahatma gandhi', 'marilyn monroe',
        'winston churchill', 'oscar wilde', 'plato', 'aristotle', 'bob marley', 'dalai lama', 'rumi'];

    private const BLOCK = ['alcohol', 'beer', 'wine', 'drunk', 'whisky', 'sex', 'damn', 'hell ', 'god damn', 'casino', 'gambl'];

    private const SENSITIVE = ['god', 'allah', 'jesus', 'christ', 'heaven', 'prayer', 'religion', 'church', 'soul', 'kill', 'war', 'death', 'die '];

    public function importNext(int $amount = 200): array
    {
        $data = Http::timeout(30)->get(self::URL)->throw()->json();
        if (! is_array($data)) {
            throw new \RuntimeException('QuoteSlate returned no list');
        }
        $offset = (int) (Setting::get('quoteslate_offset') ?? 0);
        $batch = array_slice($data, $offset, $amount);
        $stats = $this->save($batch);
        Setting::put('quoteslate_offset', $offset + count($batch) >= count($data) ? count($data) : $offset + count($batch));
        $stats['remaining'] = max(0, count($data) - $offset - count($batch));

        return $stats;
    }

    /** @param array $rows QuoteSlate rows {id, quote, author, tags} */
    public function save(array $rows): array
    {
        $stats = ['saved' => 0, 'duplicates' => 0, 'rejected' => 0, 'reasons' => []];
        foreach ($rows as $r) {
            $body = TriviaValidator::clean($r['quote'] ?? '');
            $body = trim($body, " \"“”");
            $author = TriviaValidator::clean($r['author'] ?? '');
            $tags = array_values(array_filter((array) ($r['tags'] ?? []), 'is_string'));
            $flags = [];

            $lower = ' '.mb_strtolower($body).' ';
            $reason = match (true) {
                mb_strlen($body) < 12 => 'Too short',
                mb_strlen($body) > 350 => 'Too long for a quote card',
                default => null,
            };
            foreach (self::BLOCK as $w) {
                if (! $reason && str_contains($lower, $w)) {
                    $reason = "Unsuitable word ({$w})";
                }
            }
            if ($reason) {
                $stats['rejected']++;
                $stats['reasons'][] = mb_substr($body, 0, 50).'… — '.$reason;
                continue;
            }
            if ($author === '' || strcasecmp($author, 'unknown') === 0) {
                $author = 'Anonymous';
                $flags[] = 'No known author';
            }
            if (in_array(mb_strtolower($author), self::DISPUTED, true)) {
                $flags[] = 'Often misattributed author: check the source before publishing';
            }
            foreach (self::SENSITIVE as $w) {
                if (preg_match('/\b'.preg_quote(trim($w), '/').'\b/u', $lower)) {
                    $flags[] = 'Religious or sensitive word ('.trim($w).')';
                    break;
                }
            }

            $hash = Quote::hashFor($body);
            if (Quote::where('hash', $hash)->exists()) {
                $stats['duplicates']++;
                continue;
            }
            Quote::create([
                'body' => $body, 'author' => mb_substr($author, 0, 120), 'author_slug' => Quote::slugForAuthor($author),
                'topic' => QuoteTopics::topicFor($tags), 'tags' => mb_substr(implode(',', $tags), 0, 255), 'hash' => $hash,
                'source' => 'quoteslate', 'source_ref' => isset($r['id']) ? 'quoteslate:'.$r['id'] : null,
                'status' => 'in_review', 'flags' => $flags ?: null,
            ]);
            $stats['saved']++;
        }

        return $stats;
    }
}
