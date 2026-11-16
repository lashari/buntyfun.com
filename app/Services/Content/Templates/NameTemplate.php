<?php

namespace App\Services\Content\Templates;

use App\Models\NameEntry;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Islamic names. The AI may suggest a name and a meaning, but a draft can never be
 * approved until a person ticks "verified" against one of the admin-approved
 * references in Settings. Nothing here is ever auto-published.
 */
class NameTemplate extends ContentTemplate
{
    public function type(): string { return 'name'; }

    public function label(): string { return 'Islamic name (needs human verification)'; }

    public function targetType(): string { return 'name_entry'; }

    public function ideas(int $n): array
    {
        $have = NameEntry::selectRaw('gender, count(*) as c')->groupBy('gender')->pluck('c', 'gender');
        $existing = NameEntry::pluck('name_en')->map(fn ($s) => Str::lower($s))->all();

        return collect(['male', 'female'])->sortBy(fn ($g) => (int) ($have[$g] ?? 0))
            ->flatMap(fn ($g) => array_fill(0, $n, ['gender' => $g, 'avoid' => array_slice($existing, 0, 80)]))
            ->take($n)->values()->all();
    }

    public function prompt(array $params): string
    {
        $avoid = implode(', ', $params['avoid'] ?? []);
        $refs = implode('; ', (array) Setting::get('approved_name_references')) ?: '(none set yet)';

        return <<<TXT
You help an editor of an Islamic baby-names directory. Suggest ONE well-known Muslim {$params['gender']} name that is NOT in this list: {$avoid}.
Give only meanings that are widely documented. If you are not sure of the meaning, set "confident" to false and leave "meaning" empty. Never invent a meaning, a Quranic reference or a hadith.
The editor will verify against one of: {$refs}. Put the most likely matching reference in "suggested_reference" (or empty).

Return ONLY JSON:
  "name_en": "English spelling",
  "name_ar": "Arabic script",
  "name_ur": "Urdu script",
  "meaning": "plain meaning, 3-25 words",
  "origin": "Arabic / Persian / Turkish / ...",
  "confident": true or false,
  "suggested_reference": "...",
  "seo_title": "max 60 chars, e.g. 'Ayaan Name Meaning in Urdu & English'",
  "seo_description": "120-155 characters",
  "faq": [],
  "social_caption": "one caption, max 2 hashtags",
  "image_brief": "simple calligraphy-style card idea, no real people"
TXT;
    }

    protected function normalizeFields(array $json, array $params): array
    {
        return [
            'name_en' => self::clean($json['name_en'] ?? '', 60),
            'name_ar' => self::clean($json['name_ar'] ?? '', 60),
            'name_ur' => self::clean($json['name_ur'] ?? '', 60),
            'gender' => self::pick($params['gender'] ?? 'male', ['male', 'female', 'unisex'], 'male'),
            'meaning' => self::clean($json['meaning'] ?? '', 400),
            'origin' => self::clean($json['origin'] ?? '', 60),
            'ai_confident' => (bool) ($json['confident'] ?? false),
            'source_ref' => self::clean($json['suggested_reference'] ?? '', 500),
            'verified_by_admin' => false,
        ];
    }

    public function fields(): array
    {
        return [
            'name_en' => ['Name (English)', 'text'],
            'name_ar' => ['Arabic', 'text'],
            'name_ur' => ['Urdu', 'text'],
            'gender' => ['Gender', 'select', ['male', 'female', 'unisex']],
            'meaning' => ['Meaning', 'textarea'],
            'origin' => ['Origin', 'text'],
            'source_ref' => ['Verified in (must be an approved reference)', 'text'],
            'verified_by_admin' => ['I checked this meaning myself in that reference', 'checkbox'],
        ];
    }

    public function comparableText(array $payload): string { return Str::lower((string) ($payload['name_en'] ?? '')); }

    public function existingTexts(): Collection
    {
        return NameEntry::pluck('name_en')->map(fn ($s) => Str::lower($s));
    }

    public function wordRange(): array { return [1, 3]; }

    public function language(array $payload): string { return 'en'; }

    public function extraChecks(array $payload): array
    {
        $refs = collect((array) Setting::get('approved_name_references'))->map(fn ($r) => Str::lower(trim($r)))->filter();
        $ref = Str::lower(trim((string) ($payload['source_ref'] ?? '')));

        return [
            ['key' => 'meaning_present', 'label' => 'Meaning given', 'ok' => trim((string) ($payload['meaning'] ?? '')) !== '', 'blocking' => true,
                'note' => ($payload['ai_confident'] ?? false) ? null : 'AI was not confident about this meaning.'],
            ['key' => 'approved_reference', 'label' => 'Source is an approved reference', 'ok' => $ref !== '' && $refs->contains(fn ($r) => str_contains($ref, $r)), 'blocking' => true,
                'note' => $refs->isEmpty() ? 'No approved references set yet (Automation → Settings).' : null],
            ['key' => 'human_verified', 'label' => 'Verified by a person', 'ok' => (bool) ($payload['verified_by_admin'] ?? false), 'blocking' => true,
                'note' => 'Religious content is never published on AI output alone.'],
        ];
    }

    public function create(array $payload, string $status): Model
    {
        $slug = Str::slug($payload['name_en']);
        if (NameEntry::where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        return NameEntry::create([
            'name_en' => $payload['name_en'],
            'name_ar' => $payload['name_ar'] ?: null,
            'name_ur' => $payload['name_ur'] ?: null,
            'gender' => $payload['gender'],
            'meaning' => $payload['meaning'],
            'origin' => $payload['origin'] ?: null,
            'source_ref' => $payload['source_ref'],
            'slug' => $slug,
            'status' => $status,
        ]);
    }
}
