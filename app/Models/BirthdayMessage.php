<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BirthdayMessage extends Model
{
    use \App\Models\Concerns\HasContentMeta;

    protected $fillable = ['category_id', 'relationship', 'tone', 'language', 'body', 'slug', 'status'];

    /** Relationships that have a written guide and at least 3 published wishes get a hub page. */
    public static function hubRelationships(): array
    {
        return static::where('status', 'published')
            ->selectRaw('relationship, count(*) as n')->groupBy('relationship')->having('n', '>=', 3)
            ->pluck('relationship')
            ->filter(fn ($r) => \App\Content\BirthdayGuides::for($r) !== null)
            ->sort()->values()->all();
    }

    /** /birthday-wishes/urdu is only worth indexing once it holds a real collection. */
    public const URDU_PAGE_MIN = 10;

    public static function urduPageIndexable(): bool
    {
        return static::where('status', 'published')->where('language', 'ur')->count() >= self::URDU_PAGE_MIN;
    }

    public static function relationshipSlug(string $relationship): string
    {
        return str_replace('_', '-', $relationship);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
