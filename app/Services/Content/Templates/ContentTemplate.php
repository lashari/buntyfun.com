<?php

namespace App\Services\Content\Templates;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * One per content type the AI engine can draft. A template knows how to pick gaps,
 * write the prompt, clean the model's JSON, check it, and create the real row.
 */
abstract class ContentTemplate
{
    abstract public function type(): string;

    abstract public function label(): string;

    /** Morph alias of the model this creates (see AppServiceProvider morph map). */
    abstract public function targetType(): string;

    /** Up to $n parameter sets, emptiest gaps first. */
    abstract public function ideas(int $n): array;

    abstract public function prompt(array $params): string;

    /** Type-specific fields, cleaned from the model's JSON. */
    abstract protected function normalizeFields(array $json, array $params): array;

    /** [field => [label, kind (text|textarea|select|checkbox), options?]] for the admin edit form. */
    abstract public function fields(): array;

    /** Text the duplicate check compares (body, or name + meaning). */
    abstract public function comparableText(array $payload): string;

    /** Existing comparable texts (published + in review) for the duplicate check. */
    abstract public function existingTexts(): Collection;

    /** Persist the real content row with the given status. */
    abstract public function create(array $payload, string $status): Model;

    /** [min, max] words for the main text. */
    public function wordRange(): array
    {
        return [12, 120];
    }

    public function language(array $payload): string
    {
        return $payload['language'] ?? 'en';
    }

    /** Extra checks: list of ['key','label','ok','blocking','note']. */
    public function extraChecks(array $payload): array
    {
        return [];
    }

    public function normalize(array $json, array $params): array
    {
        $faq = collect($json['faq'] ?? [])
            ->filter(fn ($f) => is_array($f) && trim((string) ($f['q'] ?? '')) !== '' && trim((string) ($f['a'] ?? '')) !== '')
            ->map(fn ($f) => ['q' => self::clean($f['q'], 200), 'a' => self::clean($f['a'], 600)])
            ->take(4)->values()->all();

        return $this->normalizeFields($json, $params) + [
            'seo_title' => self::clean($json['seo_title'] ?? '', 70),
            'seo_description' => self::clean($json['seo_description'] ?? '', 170),
            'faq' => $faq,
            'social_caption' => self::clean($json['social_caption'] ?? '', 500),
            'image_brief' => self::clean($json['image_brief'] ?? '', 500),
        ];
    }

    /** Shared tail of every prompt: output shape + house rules. */
    protected function jsonInstructions(string $fields): string
    {
        return <<<TXT

Return ONLY a JSON object with these keys:
{$fields}
  "seo_title": "page title, max 60 characters, natural, no clickbait",
  "seo_description": "meta description, 120-155 characters, says what the visitor gets",
  "faq": [{"q": "...", "a": "..."}]  (0-3 short, genuinely useful questions; empty list if none fit),
  "social_caption": "one Facebook/Instagram caption, max 2 hashtags",
  "image_brief": "one sentence describing a simple original image for this post (no real people, no logos)"

Rules: write original text (never copy known quotes, poems, songs or lyrics), warm and natural like a real person, no keyword stuffing, no links, no emojis inside the main text, no mention of AI. Family-friendly.
TXT;
    }

    public static function clean(mixed $v, int $max): string
    {
        $s = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $v)));

        return mb_substr($s, 0, $max);
    }

    /** Pick from an option list, falling back to the requested param. */
    protected static function pick(mixed $v, array $allowed, string $fallback): string
    {
        $v = strtolower(trim((string) $v));

        return in_array($v, $allowed, true) ? $v : $fallback;
    }
}
