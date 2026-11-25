<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A Quran reciter whose per-ayah recordings are streamed unaltered from a public Quran audio CDN.
 * url_pattern placeholders: {g} = global ayah number (1–6236), {sss}{aaa} = zero-padded surah/ayah.
 */
class QuranReciter extends Model
{
    public const STYLES = ['murattal' => 'Murattal', 'mujawwad' => 'Mujawwad', 'muallim' => 'Teaching'];

    protected $fillable = ['slug', 'name', 'name_ar', 'country', 'style', 'url_pattern', 'source', 'license_note', 'sort', 'status'];

    public static function live()
    {
        return static::where('status', 'published')->orderBy('sort')->orderBy('id')->get();
    }

    public function urlFor(int $global, int $surah, int $ayah): string
    {
        return strtr($this->url_pattern, [
            '{g}' => (string) $global,
            '{sss}' => str_pad((string) $surah, 3, '0', STR_PAD_LEFT),
            '{aaa}' => str_pad((string) $ayah, 3, '0', STR_PAD_LEFT),
        ]);
    }
}
