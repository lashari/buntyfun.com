<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrduContent extends Model
{
    use \App\Models\Concerns\HasContentMeta;

    protected $fillable = ['category', 'body', 'slug', 'status'];
}
