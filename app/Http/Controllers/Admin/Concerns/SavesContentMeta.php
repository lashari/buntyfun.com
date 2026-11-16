<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * SEO + scheduling fields from admin/partials/seo-fields.blade.php, saved to content_meta.
 */
trait SavesContentMeta
{
    protected function saveMeta(Request $request, Model $model): void
    {
        $data = $request->validate([
            'meta_seo_title' => ['nullable', 'string', 'max:70'],
            'meta_seo_description' => ['nullable', 'string', 'max:170'],
            'meta_og_image' => ['nullable', 'url', 'max:255'],
            'meta_faq' => ['nullable', 'string', 'max:5000'],
            'meta_publish_at' => ['nullable', 'date'],
        ]);

        $faq = collect(preg_split('/\R/', (string) ($data['meta_faq'] ?? '')))
            ->map(fn ($line) => array_map('trim', explode('||', $line, 2)))
            ->filter(fn ($p) => count($p) === 2 && $p[0] !== '' && $p[1] !== '')
            ->map(fn ($p) => ['q' => $p[0], 'a' => $p[1]])->take(6)->values()->all();

        $fields = [
            'seo_title' => $data['meta_seo_title'] ?? null,
            'seo_description' => $data['meta_seo_description'] ?? null,
            'og_image' => $data['meta_og_image'] ?? null,
            'faq' => $faq ?: null,
            'is_featured' => $request->boolean('meta_is_featured'),
            'is_trending' => $request->boolean('meta_is_trending'),
            'noindex' => $request->boolean('meta_noindex'),
        ];

        // Only publishers may schedule. A future date holds the item in review until the
        // publish_scheduled job releases it.
        $meta = $model->metaOrNew();
        if ($request->user()->canPublish()) {
            $at = ! empty($data['meta_publish_at']) ? Carbon::parse($data['meta_publish_at']) : null;
            $fields['publish_at'] = $at;
            if ($at && $at->isFuture() && $model->status === 'published') {
                $model->update(['status' => 'in_review']);
                AuditLog::record($request->user(), 'scheduled', $model->getMorphClass(), $model->getKey());
            }
        }

        $meta->fill($fields)->save();
    }
}
