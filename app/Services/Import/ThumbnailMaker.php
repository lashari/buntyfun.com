<?php

namespace App\Services\Import;

use App\Models\Wallpaper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Small copies so pages don't load multi-MB originals:
 *   thumbs/{id}.jpg   640 px wide  — grids, home, admin
 *   previews/{id}.jpg 1600 px long — the wallpaper page and share image
 * The original stays for Download. Very big originals are not opened with GD
 * (memory on shared hosting); a smaller copy is fetched from the source instead.
 */
class ThumbnailMaker
{
    private const LOCAL_MAX = 3200;

    public static function thumbPath(Wallpaper $w): string
    {
        return "wallpapers/thumbs/{$w->id}.jpg";
    }

    public static function previewPath(Wallpaper $w): string
    {
        return "wallpapers/previews/{$w->id}.jpg";
    }

    public static function forget(Wallpaper $w): void
    {
        Storage::disk('public')->delete([self::thumbPath($w), self::previewPath($w)]);
    }

    public static function has(Wallpaper $w): bool
    {
        return Storage::disk('public')->exists(self::thumbPath($w));
    }

    /** @return string|null error, null on success */
    public function make(Wallpaper $w): ?string
    {
        if ($w->remote_url || $w->file_path === '') {
            return 'hotlinked, no local file';
        }
        @ini_set('memory_limit', '512M');
        $disk = Storage::disk('public');
        $tmp = null;

        try {
            $source = $disk->path($w->file_path);
            if (max($w->width, $w->height) > self::LOCAL_MAX) {
                $url = $this->smallerSourceUrl($w);
                if (! $url) {
                    return 'original too big and no smaller copy at the source';
                }
                $tmp = tempnam(sys_get_temp_dir(), 'thb');
                Http::timeout(60)->withUserAgent('BuntyFunImporter/1.0 (https://buntyfun.com)')->sink($tmp)->get($url)->throw();
                $info = @getimagesize($tmp);
                if (! $info || max($info[0], $info[1]) > self::LOCAL_MAX + 800) {
                    return 'source copy unusable';
                }
                $source = $tmp;
            }

            $img = @imagecreatefromstring(file_get_contents($source));
            if (! $img) {
                return 'cannot read image';
            }
            $this->save($img, 1600, false, $disk->path(self::previewPath($w)));
            $this->save($img, 640, true, $disk->path(self::thumbPath($w)));
            imagedestroy($img);

            return null;
        } catch (\Throwable $e) {
            return mb_substr($e->getMessage(), 0, 200);
        } finally {
            if ($tmp) {
                @unlink($tmp);
            }
        }
    }

    /** $byWidth: fit width (thumb); otherwise fit the long side (preview). Never upscales. */
    private function save(\GdImage $img, int $size, bool $byWidth, string $path): void
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1, $byWidth ? $size / $w : $size / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));
        $out = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        @mkdir(dirname($path), 0755, true);
        imageinterlace($out, true);
        imagejpeg($out, $path, 80);
        imagedestroy($out);
    }

    private function smallerSourceUrl(Wallpaper $w): ?string
    {
        [$source, $id] = array_pad(explode(':', (string) $w->source_key, 2), 2, null);
        if ($source === 'nasa' && $id) {
            $assets = Http::timeout(30)->acceptJson()->get('https://images-api.nasa.gov/asset/'.rawurlencode($id))
                ->json('collection.items', []);
            foreach (['~large.jpg', '~medium.jpg'] as $suffix) {
                foreach ($assets as $a) {
                    $href = preg_replace('#^http://#', 'https://', $a['href'] ?? '');
                    if (str_ends_with(strtolower($href), $suffix)) {
                        return $href;
                    }
                }
            }
        }
        if ($source === 'wikimedia' && ctype_digit((string) $id)) {
            $pages = Http::timeout(30)->withUserAgent('BuntyFunImporter/1.0 (https://buntyfun.com)')
                ->get('https://commons.wikimedia.org/w/api.php', [
                    'action' => 'query', 'format' => 'json', 'formatversion' => 2, 'pageids' => $id,
                    'prop' => 'imageinfo', 'iiprop' => 'url',
                    // long side ≈ 2000 px for portrait and landscape alike
                    'iiurlwidth' => max(200, (int) round(2000 * $w->width / max($w->width, $w->height))),
                ])->json('query.pages', []);

            return $pages[0]['imageinfo'][0]['thumburl'] ?? null;
        }

        return null;
    }
}
