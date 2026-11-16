<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentMeta extends Model
{
    protected $table = 'content_meta';

    protected $fillable = [
        'metable_type', 'metable_id', 'seo_title', 'seo_description', 'og_image', 'faq',
        'is_featured', 'is_trending', 'noindex', 'publish_at',
    ];

    protected function casts(): array
    {
        return [
            'faq' => 'array',
            'is_featured' => 'boolean',
            'is_trending' => 'boolean',
            'noindex' => 'boolean',
            'publish_at' => 'datetime',
        ];
    }

    public function metable(): MorphTo
    {
        return $this->morphTo();
    }
}
