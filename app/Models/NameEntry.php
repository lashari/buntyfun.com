<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NameEntry extends Model
{
    use \App\Models\Concerns\HasContentMeta;

    protected $fillable = [
        'name_en', 'name_ar', 'name_ur', 'gender', 'meaning', 'meaning_ur', 'origin', 'category', 'quranic_ref', 'source_ref', 'verification', 'slug', 'status',
    ];

    /** Name lists with their own page under /islamic-names/{key}. gender lists filter by gender; the rest by category. */
    public const LISTS = [
        'boys' => ['title' => 'Muslim Boys Names', 'gender' => 'male', 'intro' => 'Muslim names for boys with Arabic and Urdu spelling and meaning. Every meaning cites a source you can check.'],
        'girls' => ['title' => 'Muslim Girls Names', 'gender' => 'female', 'intro' => 'Muslim names for girls with Arabic and Urdu spelling and meaning. Every meaning cites a source you can check.'],
        'quranic' => ['title' => 'Quranic Names', 'category' => 'quranic', 'intro' => 'Names that are words used in the Quran, each with the verse it appears in. The verse reference links to the full text so you can read it in context.'],
        'prophets' => ['title' => 'Names of the Prophets', 'category' => 'prophet', 'intro' => 'The 25 prophets mentioned by name in the Quran, with Arabic and Urdu spelling and a verse in which each is named. Peace be upon them all.'],
        'sahaba' => ['title' => 'Sahaba Names (Companions)', 'category' => 'sahaba', 'intro' => 'Names of well-known Companions of the Prophet Muhammad ﷺ, men and women, with the meaning of each name and a link to read about the Companion.'],
    ];

    public const CATEGORIES = ['general' => 'General', 'quranic' => 'Quranic word', 'prophet' => 'Prophet', 'sahaba' => 'Companion (Sahabi)', 'sahabiyah' => 'Companion (Sahabiyah)'];
}
