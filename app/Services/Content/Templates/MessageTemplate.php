<?php

namespace App\Services\Content\Templates;

use App\Models\Message;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MessageTemplate extends ContentTemplate
{
    public function type(): string { return 'message'; }

    public function label(): string { return 'Message / quote'; }

    public function targetType(): string { return 'message'; }

    public function ideas(int $n): array
    {
        $have = Message::whereIn('status', ['published', 'in_review'])
            ->selectRaw('category, count(*) as c')->groupBy('category')->pluck('c', 'category');

        $month = (int) now()->month;
        $seasonBoost = ['new_year' => in_array($month, [11, 12, 1], true)];

        return collect(array_keys(Message::CATEGORIES))
            ->map(fn ($c) => ['category' => $c, 'language' => 'en', '_score' => (int) ($have[$c] ?? 0) - (($seasonBoost[$c] ?? false) ? 5 : 0)])
            ->shuffle()->sortBy('_score')->take($n)
            ->map(fn ($c) => ['category' => $c['category'], 'language' => $c['language']])->values()->all();
    }

    public function prompt(array $params): string
    {
        $label = Message::CATEGORIES[$params['category']][0] ?? $params['category'];
        $extra = $params['category'] === 'condolence'
            ? 'This is a condolence message: gentle, respectful, short, no jokes, no religious rulings, suitable for any family.'
            : 'Keep it warm and easy to send to someone.';
        $lang = ($params['language'] ?? 'en') === 'ur' ? 'Write the message in Urdu script; SEO fields in English.' : 'Write in simple, natural English.';

        return <<<TXT
You write short shareable messages for BuntyFun.com (family-friendly, readers in Pakistan, India, UK, US).
Category: {$label}. Write ONE original message, 20-80 words, plus a short title (max 8 words). {$extra} {$lang}
Do not quote famous people and do not attribute words to anyone.
TXT
            .$this->jsonInstructions("  \"title\": \"short title\",\n  \"body\": \"the message\",");
    }

    protected function normalizeFields(array $json, array $params): array
    {
        return [
            'category' => self::pick($params['category'], array_keys(Message::CATEGORIES), 'friendship'),
            'language' => self::pick($params['language'] ?? 'en', ['en', 'ur'], 'en'),
            'title' => self::clean($json['title'] ?? '', 120),
            'body' => trim((string) ($json['body'] ?? '')),
        ];
    }

    public function fields(): array
    {
        return [
            'category' => ['Category', 'select', array_keys(Message::CATEGORIES)],
            'language' => ['Language', 'select', ['en', 'ur']],
            'title' => ['Title', 'text'],
            'body' => ['Message', 'textarea'],
        ];
    }

    public function comparableText(array $payload): string { return (string) ($payload['body'] ?? ''); }

    public function existingTexts(): Collection
    {
        return Message::whereIn('status', ['published', 'in_review'])->pluck('body');
    }

    public function wordRange(): array { return [12, 110]; }

    public function create(array $payload, string $status): Model
    {
        return Message::create([
            'category' => $payload['category'],
            'language' => $payload['language'],
            'title' => $payload['title'] ?: null,
            'body' => $payload['body'],
            'slug' => Str::slug(($payload['title'] ?: $payload['category']).'-'.Str::random(5)),
            'status' => $status,
        ]);
    }
}
