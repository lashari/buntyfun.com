<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyFeature extends Model
{
    public const TYPES = ['quiz' => 'Quiz of the day', 'quote' => 'Quote of the day', 'game' => 'Game of the day'];

    protected $fillable = ['feature_date', 'type', 'item_id', 'pinned'];

    protected $casts = ['feature_date' => 'date:Y-m-d', 'pinned' => 'boolean'];

    /** Insert or update the row for one date + type (matched by date, whatever time part the driver stores). */
    public static function setFor(string $date, string $type, array $values): self
    {
        $row = static::whereDate('feature_date', $date)->where('type', $type)->first() ?? new static(['feature_date' => $date, 'type' => $type]);
        $row->fill($values)->save();

        return $row;
    }
}
