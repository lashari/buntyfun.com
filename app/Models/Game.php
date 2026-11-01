<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = ['category_id', 'slug', 'title', 'config_json', 'status'];

    protected $casts = [
        'config_json' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(GameScore::class);
    }
}
