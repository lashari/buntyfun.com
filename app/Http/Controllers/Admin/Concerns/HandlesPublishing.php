<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait HandlesPublishing
{
    /**
     * Editors can only save as draft or submit for review. Only admins/super admins
     * (User::canPublish()) may set status to published.
     */
    protected function sanitizeStatus(Request $request, string $requested): string
    {
        if (! in_array($requested, ['draft', 'in_review', 'published'], true)) {
            $requested = 'draft';
        }

        if ($requested === 'published' && ! $request->user()->canPublish()) {
            return 'in_review';
        }

        return $requested;
    }

    protected function publishModel(Request $request, Model $model, string $entityType): void
    {
        abort_unless($request->user()->canPublish(), 403);

        $model->update(['status' => 'published']);
        AuditLog::record($request->user(), 'published', $entityType, $model->id);
    }

    protected function unpublishModel(Request $request, Model $model, string $entityType): void
    {
        abort_unless($request->user()->canPublish(), 403);

        $model->update(['status' => 'draft']);
        AuditLog::record($request->user(), 'unpublished', $entityType, $model->id);
    }
}
