<?php

namespace App\Services\Content;

use App\Models\AiDraft;
use App\Models\AuditLog;
use App\Models\ContentMeta;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DraftApprover
{
    /**
     * Turn a draft into a real content row.
     *  mode 'now'      → published at once (admins only)
     *  mode 'schedule' → in_review + publish_at; the publish_scheduled job publishes it (admins only)
     *  mode 'review'   → in_review, no date (editors may do this)
     *
     * Runs in a transaction with the draft row locked, so a double click or two
     * admins at once can never create the item twice.
     */
    public function approve(AiDraft $draft, User $user, string $mode, ?Carbon $publishAt = null): AiDraft
    {
        if (in_array($mode, ['now', 'schedule'], true) && ! $user->canPublish()) {
            throw new RuntimeException('Only admins can publish or schedule.');
        }
        if ($mode === 'schedule' && (! $publishAt || $publishAt->isPast())) {
            throw new RuntimeException('Pick a publish time in the future.');
        }

        return DB::transaction(function () use ($draft, $user, $mode, $publishAt) {
            $locked = AiDraft::whereKey($draft->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'generated') {
                throw new RuntimeException("This draft is already {$locked->status}.");
            }
            if ($locked->hasBlockingFailure()) {
                throw new RuntimeException('Fix the failed checks first (edit, then save to re-check).');
            }

            $template = TemplateRegistry::get($locked->content_type);
            $payload = $locked->payload;
            $item = $template->create($payload, $mode === 'now' ? 'published' : 'in_review');

            ContentMeta::create([
                'metable_type' => $item->getMorphClass(),
                'metable_id' => $item->getKey(),
                'seo_title' => $payload['seo_title'] ?: null,
                'seo_description' => $payload['seo_description'] ?: null,
                'faq' => $payload['faq'] ?: null,
                'publish_at' => $mode === 'schedule' ? $publishAt : null,
            ]);

            $locked->update([
                'status' => 'approved', 'target_type' => $item->getMorphClass(), 'target_id' => $item->getKey(),
                'reviewed_by' => $user->id, 'reviewed_at' => now(),
            ]);
            AuditLog::record($user, 'approved_ai_draft:'.$mode, $item->getMorphClass(), $item->getKey());

            return $locked;
        });
    }

    public function reject(AiDraft $draft, User $user, ?string $note): void
    {
        abort_unless($draft->status === 'generated' || $draft->status === 'failed', 422, 'Draft already reviewed.');
        $draft->update(['status' => 'rejected', 'reviewed_by' => $user->id, 'reviewed_at' => now(), 'review_note' => $note]);
    }
}
