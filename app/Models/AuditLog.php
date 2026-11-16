<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'entity_type', 'entity_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Automation (cron) actions: no human user. */
    public static function recordSystem(string $action, string $entityType, int $entityId): self
    {
        return self::create(['user_id' => null, 'action' => 'auto:'.$action, 'entity_type' => $entityType, 'entity_id' => $entityId]);
    }

    public static function record(User $user, string $action, string $entityType, int $entityId): self
    {
        return self::create([
            'user_id' => $user->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);
    }
}
