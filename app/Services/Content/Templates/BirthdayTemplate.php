<?php

namespace App\Services\Content\Templates;

use App\Models\BirthdayMessage;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BirthdayTemplate extends ContentTemplate
{
    public const RELATIONSHIPS = ['best_friend', 'friend', 'mother', 'father', 'sister', 'brother', 'wife', 'husband', 'son', 'daughter', 'boss', 'colleague', 'grandmother', 'grandfather'];

    public const TONES = ['funny', 'emotional', 'short', 'heartfelt', 'formal'];

    public function type(): string { return 'birthday'; }

    public function label(): string { return 'Birthday wish'; }

    public function targetType(): string { return 'birthday_message'; }

    public function ideas(int $n): array
    {
        $have = BirthdayMessage::whereIn('status', ['published', 'in_review'])
            ->selectRaw('relationship, tone, language, count(*) as c')
            ->groupBy('relationship', 'tone', 'language')->get()
            ->keyBy(fn ($r) => "{$r->relationship}|{$r->tone}|{$r->language}");

        $combos = [];
        foreach (self::RELATIONSHIPS as $r) {
            foreach (self::TONES as $t) {
                foreach (['en', 'ur'] as $l) {
                    if ($l === 'ur' && $t === 'formal') continue;
                    $count = (int) ($have["$r|$t|$l"]->c ?? 0);
                    // Urdu weighted down: English pages get most of the search demand.
                    $combos[] = ['relationship' => $r, 'tone' => $t, 'language' => $l, '_score' => $count + ($l === 'ur' ? 2 : 0)];
                }
            }
        }

        return collect($combos)->shuffle()->sortBy('_score')->take($n)
            ->map(fn ($c) => collect($c)->except('_score')->all())->values()->all();
    }

    public function prompt(array $params): string
    {
        $who = str_replace('_', ' ', $params['relationship']);
        $lang = ($params['language'] ?? 'en') === 'ur'
            ? 'Write the wish in Urdu (Urdu script, not Roman Urdu). SEO title/description/FAQ in English.'
            : 'Write in simple, natural English.';

        return <<<TXT
You write birthday wishes for BuntyFun.com, a family-friendly site used mostly in Pakistan, India, the UK and the US.
Write ONE original {$params['tone']} birthday wish for a {$who}. 30-90 words. {$lang}
It should feel personal and specific to the relationship, not generic.
TXT
            .$this->jsonInstructions('  "body": "the wish",');
    }

    protected function normalizeFields(array $json, array $params): array
    {
        return [
            'relationship' => self::pick($json['relationship'] ?? $params['relationship'], self::RELATIONSHIPS, $params['relationship']),
            'tone' => self::pick($json['tone'] ?? $params['tone'], self::TONES, $params['tone']),
            'language' => self::pick($params['language'] ?? 'en', ['en', 'ur'], 'en'),
            'body' => trim((string) ($json['body'] ?? '')),
        ];
    }

    public function fields(): array
    {
        return [
            'relationship' => ['Relationship', 'select', self::RELATIONSHIPS],
            'tone' => ['Tone', 'select', self::TONES],
            'language' => ['Language', 'select', ['en', 'ur']],
            'body' => ['Wish', 'textarea'],
        ];
    }

    public function comparableText(array $payload): string { return (string) ($payload['body'] ?? ''); }

    public function existingTexts(): Collection
    {
        return BirthdayMessage::whereIn('status', ['published', 'in_review'])->pluck('body');
    }

    public function wordRange(): array { return [12, 130]; }

    public function create(array $payload, string $status): Model
    {
        $category = Category::where('department', 'birthday_wishes')->first()
            ?? Category::create(['department' => 'birthday_wishes', 'slug' => 'birthday-wishes', 'name' => 'Birthday Wishes']);

        return BirthdayMessage::create([
            'category_id' => $category->id,
            'relationship' => $payload['relationship'],
            'tone' => $payload['tone'],
            'language' => $payload['language'],
            'body' => $payload['body'],
            'slug' => Str::slug($payload['relationship'].'-'.$payload['tone'].'-'.Str::random(6)),
            'status' => $status,
        ]);
    }
}
