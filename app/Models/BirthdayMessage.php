<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BirthdayMessage extends Model
{
    use \App\Models\Concerns\HasContentMeta;

    protected $fillable = ['category_id', 'relationship', 'tone', 'language', 'body', 'slug', 'status'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
