<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllahName extends Model
{
    protected $fillable = ['number', 'arabic', 'transliteration', 'meaning_en', 'meaning_ur', 'quran_ref', 'status'];
}
