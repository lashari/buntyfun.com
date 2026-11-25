<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Naat extends Model
{
    use \App\Models\Concerns\HasContentMeta;

    public const CATEGORIES = [
        'urdu' => 'Urdu naats', 'arabic' => 'Arabic nasheeds & qasidas', 'punjabi' => 'Punjabi naats',
        'hamd' => 'Hamd (praise of Allah)', 'salam' => 'Salam', 'kids' => 'Naats for children',
    ];

    protected $fillable = ['slug', 'title', 'title_ur', 'category', 'poet', 'reciter', 'lyrics', 'description', 'youtube_id', 'license', 'source_url', 'status'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function audio()
    {
        return $this->hasOne(AudioTrack::class, 'ref_id')->where('kind', 'naat')->where('status', 'published')->latest('id');
    }
}
