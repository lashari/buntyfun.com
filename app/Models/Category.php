<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['department', 'slug', 'name', 'parent_id', 'ads_enabled'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function birthdayMessages(): HasMany
    {
        return $this->hasMany(BirthdayMessage::class);
    }
}
