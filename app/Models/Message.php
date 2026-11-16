<?php

namespace App\Models;

use App\Models\Concerns\HasContentMeta;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasContentMeta;

    protected $fillable = ['category', 'language', 'title', 'body', 'slug', 'status'];

    /** category => [label, ads allowed]. Condolence never carries ads. */
    public const CATEGORIES = [
        'good_morning' => ['Good Morning Messages', true],
        'good_night' => ['Good Night Messages', true],
        'congratulations' => ['Congratulations Messages', true],
        'anniversary' => ['Anniversary Wishes', true],
        'eid' => ['Eid Mubarak Messages', true],
        'ramadan' => ['Ramadan Messages', true],
        'new_year' => ['New Year Wishes', true],
        'friendship' => ['Friendship Quotes', true],
        'motivational' => ['Motivational Quotes', true],
        'thank_you' => ['Thank You Messages', true],
        'get_well' => ['Get Well Soon Messages', true],
        'condolence' => ['Condolence Messages', false],
    ];

    /** Dated categories: reviewed by the weekly content_review job. */
    public const SEASONAL = ['eid', 'ramadan', 'new_year'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category][0] ?? ucwords(str_replace('_', ' ', $this->category));
    }

    public function adsAllowed(): bool
    {
        return self::CATEGORIES[$this->category][1] ?? true;
    }
}
