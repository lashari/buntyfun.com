<?php

namespace App\Services\Content;

use App\Models\AuditLog;
use App\Models\ContentMeta;
use App\Models\NameEntry;
use Illuminate\Support\Facades\DB;

class ScheduledPublisher
{
    /** Publishes due items. Returns [published count, list of skipped reasons]. */
    public function run(): array
    {
        $done = 0;
        $skipped = [];

        $due = ContentMeta::whereNotNull('publish_at')->where('publish_at', '<=', now())->with('metable')->get();
        foreach ($due as $meta) {
            $item = $meta->metable;
            if (! $item || $item->status !== 'in_review') {
                continue; // already published, unpublished by hand, or deleted
            }
            if ($item instanceof NameEntry && (empty($item->source_ref) || stripos($item->source_ref, 'placeholder') !== false)) {
                $skipped[] = "name #{$item->id}: no verified source";
                continue;
            }

            // Conditional update = no double publish even if two runs overlap.
            $changed = DB::table($item->getTable())->where('id', $item->id)->where('status', 'in_review')
                ->update(['status' => 'published', 'updated_at' => now()]);
            if ($changed) {
                AuditLog::recordSystem('published_scheduled', $item->getMorphClass(), $item->id);
                $done++;
            }
        }

        return [$done, $skipped];
    }
}
