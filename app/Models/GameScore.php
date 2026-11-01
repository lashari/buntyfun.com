<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameScore extends Model
{
    protected $fillable = ['game_id', 'nickname', 'score'];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
