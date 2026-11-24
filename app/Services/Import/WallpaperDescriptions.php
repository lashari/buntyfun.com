<?php

namespace App\Services\Import;

use App\Models\Wallpaper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Real captions for wallpapers, taken from the image's own source page:
 * NASA Image Library (public domain text) or Wikimedia Commons (shown with a link back).
 * No text is invented: if the source has no usable caption, the description stays ''.
 */
class WallpaperDescriptions
{
    public const MAX_CHARS = 700;

    /** Fetch + clean the caption for one wallpaper. Returns '' when the source has none. */
    public function fetch(Wallpaper $wallpaper): string
    {
        [$source, $id] = array_pad(explode(':', (string) $wallpaper->source_key, 2), 2, null);

        $raw = match ($source) {
            'nasa' => $this->nasa($id),
            'wikimedia' => ctype_digit((string) $id) ? $this->wikimedia((int) $id) : null,
            default => null,
        };

        return self::clean($raw, $wallpaper->title);
    }

    private function nasa(?string $id): ?string
    {
        if (! $id) {
            return null;
        }
        $data = Http::timeout(20)->acceptJson()->get('https://images-api.nasa.gov/search', ['nasa_id' => $id])
            ->throw()->json('collection.items.0.data.0', []);

        return $data['description'] ?? ($data['description_508'] ?? null);
    }

    private function wikimedia(int $pageId): ?string
    {
        $page = Http::timeout(20)->withUserAgent('BuntyFunImporter/1.0 (https://buntyfun.com)')
            ->get('https://commons.wikimedia.org/w/api.php', [
                'action' => 'query', 'format' => 'json', 'formatversion' => 2, 'pageids' => $pageId,
                'prop' => 'imageinfo', 'iiprop' => 'extmetadata',
                'iiextmetadatafilter' => 'ImageDescription', 'iiextmetadatalanguage' => 'en',
            ])->throw()->json('query.pages.0', []);

        return $page['imageinfo'][0]['extmetadata']['ImageDescription']['value'] ?? null;
    }

    /**
     * Plain text, no links or credit lines, cut at a sentence end near MAX_CHARS.
     * Returns '' for captions that only repeat the title or are too short to help a reader.
     */
    public static function clean(?string $raw, string $title = ''): string
    {
        $raw = self::fixMojibake((string) $raw);
        $text = html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], ' ', (string) $raw)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('#https?://\S+#u', '', $text);
        // Drop trailing credit / caption boilerplate that the page already shows.
        $text = preg_replace('/\b(Image|Photo|Video)?\s*Credits?\s*:.*$/isu', '', $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text), " \t\n\r\0\x0B-–—|");
        // Link lead-ins left dangling once the URL is gone ("… clouds. See", "For more information, visit").
        $text = trim(preg_replace('/(?:\s|^)(?:see|visit|read more|more info(?:rmation)?|for more information,?(?: visit| see)?)\s*[:.]?$/iu', '', $text));

        if (mb_strlen($text) > self::MAX_CHARS) {
            $cut = mb_substr($text, 0, self::MAX_CHARS);
            $end = max(mb_strrpos($cut, '. ') ?: 0, mb_strrpos($cut, '! ') ?: 0, mb_strrpos($cut, '? ') ?: 0);
            $text = $end > 200 ? mb_substr($cut, 0, $end + 1) : rtrim(Str::limit($cut, self::MAX_CHARS - 1, ''), ' ,;:').'…';
        }

        if (str_word_count($text) < 8 || Str::lower($text) === Str::lower(trim($title))) {
            return '';
        }

        return $text;
    }

    /** Windows-1252 bytes 0x80–0x9F that map to characters above U+00FF. */
    private const CP1252 = [
        '€' => 0x80, '‚' => 0x82, 'ƒ' => 0x83, '„' => 0x84, '…' => 0x85, '†' => 0x86, '‡' => 0x87, 'ˆ' => 0x88,
        '‰' => 0x89, 'Š' => 0x8A, '‹' => 0x8B, 'Œ' => 0x8C, 'Ž' => 0x8E, '‘' => 0x91, '’' => 0x92, '“' => 0x93,
        '”' => 0x94, '•' => 0x95, '–' => 0x96, '—' => 0x97, '˜' => 0x98, '™' => 0x99, 'š' => 0x9A, '›' => 0x9B,
        'œ' => 0x9C, 'ž' => 0x9E, 'Ÿ' => 0x9F,
    ];

    /**
     * Repair UTF-8 that the source decoded as Latin-1 or Windows-1252
     * ("â€œEmerald Isleâ€" → “Emerald Isle”). Each broken sequence is turned back into
     * bytes and kept only if those bytes are valid UTF-8, so normal text is never touched.
     */
    public static function fixMojibake(string $text): string
    {
        $cont = '[\x{80}-\x{BF}'.implode('', array_keys(self::CP1252)).']';

        return preg_replace_callback('/[\x{C2}-\x{F4}]'.$cont.'{1,3}/u', function ($m) {
            $bytes = '';
            foreach (mb_str_split($m[0]) as $ch) {
                $code = mb_ord($ch);
                $bytes .= chr($code <= 0xFF ? $code : (self::CP1252[$ch] ?? 0x3F));
            }

            return mb_check_encoding($bytes, 'UTF-8') && mb_strlen($bytes) === 1 ? $bytes : $m[0];
        }, $text) ?? $text;
    }
}
