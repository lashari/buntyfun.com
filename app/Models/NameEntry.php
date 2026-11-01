<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NameEntry extends Model
{
    protected $fillable = [
        'name_en', 'name_ar', 'name_ur', 'gender', 'meaning', 'origin', 'source_ref', 'slug', 'status',
    ];
}
