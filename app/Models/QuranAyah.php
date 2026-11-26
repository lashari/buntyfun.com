<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranAyah extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    public function surahModel()
    {
        return $this->belongsTo(QuranSurah::class, 'surah', 'number');
    }
}
