<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialPost extends Model
{
    protected $fillable = [
        'platform', 'social_account_id', 'source_type', 'source_id', 'caption', 'link', 'image_url', 'status',
        'scheduled_at', 'published_at', 'external_id', 'permalink', 'error', 'attempts', 'created_by', 'approved_by',
    ];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'published_at' => 'datetime'];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class, 'social_account_id');
    }

    /** The link with UTM tags so GA4 shows which post sent the visit. */
    public static function trackedLink(string $url, string $platform, ?int $postId = null): string
    {
        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $q);
        $q += ['utm_source' => $platform, 'utm_medium' => 'social', 'utm_campaign' => 'bf_social'];
        if ($postId) {
            $q['utm_content'] = 'post'.$postId;
        }

        return strtok($url, '?').'?'.http_build_query($q);
    }
}
