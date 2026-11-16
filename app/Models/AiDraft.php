<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDraft extends Model
{
    protected $fillable = [
        'content_type', 'params', 'prompt', 'raw_output', 'payload', 'quality_score', 'checks', 'status',
        'target_type', 'target_id', 'error', 'input_tokens', 'output_tokens', 'cost_usd',
        'automation_run_id', 'reviewed_by', 'reviewed_at', 'review_note',
    ];

    protected function casts(): array
    {
        return [
            'params' => 'array',
            'payload' => 'array',
            'checks' => 'array',
            'reviewed_at' => 'datetime',
            'cost_usd' => 'float',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** A blocking check failed: approve is refused until the payload is edited and re-checked. */
    public function hasBlockingFailure(): bool
    {
        return collect($this->checks ?? [])->contains(fn ($c) => ! $c['ok'] && ($c['blocking'] ?? false));
    }
}
