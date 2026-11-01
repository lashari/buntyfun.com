<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrduContent extends Model
{
    protected $fillable = ['category', 'body', 'slug', 'status'];
}
