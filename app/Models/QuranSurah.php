<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranSurah extends Model
{
    protected $primaryKey = 'number';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    public function ayahs()
    {
        return $this->hasMany(QuranAyah::class, 'surah', 'number')->orderBy('ayah');
    }

    /** /quran/{number}-{slug} */
    public function getRouteKey(): string
    {
        return $this->number.'-'.$this->slug;
    }

    public function url(): string
    {
        return route('quran.surah', $this->getRouteKey());
    }
}
