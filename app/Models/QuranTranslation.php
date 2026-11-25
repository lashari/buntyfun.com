<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A Quran translation edition. Shown on the site only when Published (licence checked by an admin). */
class QuranTranslation extends Model
{
    protected $fillable = ['code', 'language', 'name', 'translator', 'license', 'source_url', 'sort', 'status'];

    public static function live()
    {
        return static::where('status', 'published')->orderBy('sort')->get();
    }
}
