<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poet extends Model
{
    protected $fillable = ['slug', 'name_en', 'name_ur', 'born', 'died', 'bio_en', 'bio_ur', 'wikipedia', 'status'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function poems(): HasMany
    {
        return $this->hasMany(Poem::class);
    }

    public function years(): string
    {
        return ($this->born ?: '?').'–'.($this->died ?: '?');
    }
}
