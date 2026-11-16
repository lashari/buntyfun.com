<?php

namespace App\Models\Concerns;

use App\Models\ContentMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * SEO + scheduling fields live in content_meta (one row per item, created on demand).
 */
trait HasContentMeta
{
    public function meta(): MorphOne
    {
        return $this->morphOne(ContentMeta::class, 'metable');
    }

    public function metaOrNew(): ContentMeta
    {
        return $this->meta ?? new ContentMeta(['metable_type' => $this->getMorphClass(), 'metable_id' => $this->getKey()]);
    }

    /** Published and not marked noindex — what the sitemap and feed may list. */
    public function scopeIndexable(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereDoesntHave('meta', fn (Builder $q) => $q->where('noindex', true));
    }
}
