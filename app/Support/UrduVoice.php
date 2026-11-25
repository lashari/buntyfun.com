<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Pre-generated Urdu voice recordings (Indic Parler-TTS by AI4Bharat, Apache-2.0), female + male.
 * Files live on the public disk at tts/<voice>/<sha1>.m4a, where sha1 is of the whitespace-normalised text —
 * the same key the generator (dev-tools/urdu-tts/batch.py) uses. Edited text simply has no file until re-voiced.
 */
class UrduVoice
{
    public const VOICES = ['female' => 'Female', 'male' => 'Male'];

    public static function key(string $text): string
    {
        return sha1(trim(preg_replace('/[ \t\n\r\f\v]+/', ' ', $text)));
    }

    /** ['female' => url, 'male' => url] for the voices that exist, or null. */
    public static function src(string $text): ?array
    {
        if (trim($text) === '') {
            return null;
        }
        $disk = Storage::disk('public');
        $k = self::key($text);
        $out = [];
        foreach (array_keys(self::VOICES) as $v) {
            if ($disk->exists("tts/{$v}/{$k}.m4a")) {
                $out[$v] = $disk->url("tts/{$v}/{$k}.m4a");
            }
        }

        return $out ?: null;
    }
}
