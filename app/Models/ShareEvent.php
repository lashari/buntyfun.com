<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShareEvent extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['content_type', 'content_id', 'channel'];
}
