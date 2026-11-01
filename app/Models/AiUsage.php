<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsage extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['ai_tool_id', 'identifier'];

    public function tool(): BelongsTo
    {
        return $this->belongsTo(AiTool::class, 'ai_tool_id');
    }
}
