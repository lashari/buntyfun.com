<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dua extends Model
{
    use \App\Models\Concerns\HasContentMeta;

    public const CATEGORIES = [
        'daily' => 'Daily life', 'morning-evening' => 'Morning & evening', 'food' => 'Food & drink', 'sleep' => 'Sleep',
        'travel' => 'Travel', 'quran' => 'From the Quran', 'parents' => 'Parents & family', 'forgiveness' => 'Forgiveness', 'knowledge' => 'Knowledge & study',
    ];

    protected $fillable = ['slug', 'title', 'category', 'arabic', 'transliteration', 'translation_en', 'translation_ur', 'reference', 'source_url', 'sort', 'status'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
