<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportRunItem extends Model
{
    protected $fillable = ['import_run_id', 'source_key', 'title', 'item_type', 'item_id', 'status', 'message'];
}
