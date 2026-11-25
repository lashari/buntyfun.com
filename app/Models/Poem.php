<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Poem extends Model
{
    use \App\Models\Concerns\HasContentMeta;

    /** Form of the poem (from the source edition). */
    public const FORMS = ['ghazal' => ['Ghazals', 'غزلیں'], 'nazm' => ['Nazms', 'نظمیں'], 'rubai' => ['Rubaiyat', 'رباعیات'], 'qita' => ['Qitas', 'قطعات'], 'naat' => ['Naat & Hamd', 'نعت و حمد'], 'marsiya' => ['Marsiya', 'مرثیے']];

    /** Mood tags: set automatically from words in the poem (reviewable in admin). */
    public const MOODS = ['romantic' => ['Romantic poetry', 'رومانوی شاعری'], 'sad' => ['Sad poetry', 'اداس شاعری'], 'islamic' => ['Islamic poetry', 'اسلامی شاعری'], 'motivational' => ['Motivational poetry', 'حوصلہ افزا شاعری'], 'friendship' => ['Friendship poetry', 'دوستی شاعری']];

    protected $fillable = ['poet_id', 'slug', 'title', 'form', 'mood', 'book', 'body', 'line_count', 'hash', 'source', 'source_url', 'license', 'status'];

    public function poet(): BelongsTo
    {
        return $this->belongsTo(Poet::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Stanzas (couplets) as arrays of lines. Blank lines separate couplets in the source. */
    public function stanzas(): array
    {
        $blocks = preg_split("/\n\s*\n/u", trim(str_replace("\r", '', $this->body)));

        return array_map(fn ($b) => array_values(array_filter(array_map('trim', explode("\n", $b)), 'strlen')), $blocks);
    }

    public function firstLines(int $n = 2): string
    {
        return implode(' / ', array_slice(array_values(array_filter(array_map('trim', explode("\n", $this->body)), 'strlen')), 0, $n));
    }

    public static function hashBody(string $body): string
    {
        return sha1(preg_replace('/[\s\p{P}]+/u', '', $body));
    }
}
