<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentView extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['content_type', 'content_id'];

    private const BOT_UA = '~bot|crawl|spider|slurp|preview|facebookexternalhit|curl|wget|python|httpclient|headless|lighthouse|monitor~i';

    /** One row per human page view of an item. Feeds the draft planner and content review. */
    public static function track(Model $item): void
    {
        $ua = (string) request()->userAgent();
        if ($ua === '' || preg_match(self::BOT_UA, $ua)) {
            return;
        }

        static::create(['content_type' => $item->getMorphClass(), 'content_id' => $item->getKey()]);
    }
}
