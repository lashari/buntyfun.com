<?php

namespace App\Services\Content\Templates;

use App\Models\UrduContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UrduTemplate extends ContentTemplate
{
    // Same list the Urdu section already uses. No poetry: copyright risk.
    public const CATEGORIES = ['greetings', 'jokes', 'quotes', 'inspirational', 'love', 'friendship', 'festival'];

    public function type(): string { return 'urdu'; }

    public function label(): string { return 'Urdu entertainment'; }

    public function targetType(): string { return 'urdu_content'; }

    public function ideas(int $n): array
    {
        $have = UrduContent::whereIn('status', ['published', 'in_review'])
            ->selectRaw('category, count(*) as c')->groupBy('category')->pluck('c', 'category');

        return collect(self::CATEGORIES)->map(fn ($c) => ['category' => $c, '_score' => (int) ($have[$c] ?? 0)])
            ->shuffle()->sortBy('_score')->take($n)->map(fn ($c) => ['category' => $c['category']])->values()->all();
    }

    public function prompt(array $params): string
    {
        return <<<TXT
You write original Urdu entertainment content for BuntyFun.com (family-friendly, Pakistani readers).
Category: {$params['category']}. Write ONE original piece in Urdu script (not Roman Urdu), 15-70 words.
No poetry or shayari (never reproduce or imitate a known poet's lines), nothing about politics or sects. Jokes must be clean and not mock any group.
SEO title, description and FAQ in English.
TXT
            .$this->jsonInstructions('  "body": "the Urdu text",');
    }

    protected function normalizeFields(array $json, array $params): array
    {
        return [
            'category' => self::pick($params['category'], self::CATEGORIES, 'greetings'),
            'language' => 'ur',
            'body' => trim((string) ($json['body'] ?? '')),
        ];
    }

    public function fields(): array
    {
        return [
            'category' => ['Category', 'select', self::CATEGORIES],
            'body' => ['Urdu text', 'textarea'],
        ];
    }

    public function comparableText(array $payload): string { return (string) ($payload['body'] ?? ''); }

    public function existingTexts(): Collection
    {
        return UrduContent::whereIn('status', ['published', 'in_review'])->pluck('body');
    }

    public function wordRange(): array { return [8, 100]; }

    public function create(array $payload, string $status): Model
    {
        return UrduContent::create([
            'category' => $payload['category'],
            'body' => $payload['body'],
            'slug' => $payload['category'].'-'.Str::lower(Str::random(8)),
            'status' => $status,
        ]);
    }
}
