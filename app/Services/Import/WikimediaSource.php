<?php

namespace App\Services\Import;

use App\Models\ImportRun;
use App\Models\Wallpaper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * commons.wikimedia.org — official API, no key. Only files whose licence lets us
 * re-host unchanged with credit: public domain, CC0, CC BY, CC BY-SA. GFDL (needs
 * the full licence text), NC/ND and fair use are skipped. Credit (author + licence) is stored and shown on the page.
 */
class WikimediaSource implements ImportSource
{
    use WallpaperFields;

    private const API = 'https://commons.wikimedia.org/w/api.php';

    private const PER_PAGE = 50;

    private const MAX_BYTES = 15 * 1024 * 1024;

    public function key(): string
    {
        return 'wikimedia';
    }

    public function label(): string
    {
        return 'Wikimedia Commons';
    }

    public function website(): string
    {
        return 'https://commons.wikimedia.org/';
    }

    public function contentTypes(): array
    {
        return ['wallpaper'];
    }

    public function setupProblem(): ?string
    {
        return null;
    }

    public function stepSize(): int
    {
        return 3;
    }

    private static function http()
    {
        // Wikimedia policy: identify the client in the User-Agent.
        return Http::timeout(30)->withUserAgent('BuntyFunImporter/1.0 (https://buntyfun.com)');
    }

    /** Map Commons' licence code to ours, or null = not allowed. */
    public static function licenseFor(string $code): ?string
    {
        $code = strtolower(trim($code));
        if (preg_match('/^cc-by-sa-\d/', $code)) {
            return 'cc_by_sa'; // fine to share the image unchanged with credit + licence
        }
        if (str_contains($code, '-sa') || str_contains($code, 'gfdl') || str_contains($code, '-nc') || str_contains($code, '-nd')) {
            return null;
        }
        if ($code === 'cc0' || str_starts_with($code, 'cc-zero')) {
            return 'cc0';
        }
        if ($code === 'pd' || str_starts_with($code, 'pd-') || str_starts_with($code, 'public domain')) {
            return 'public_domain';
        }
        if (preg_match('/^cc-by-\d/', $code)) {
            return 'cc_by';
        }

        return null;
    }

    public function search(string $query, int $page, array $options): array
    {
        $response = self::http()->get(self::API, [
            'action' => 'query', 'format' => 'json', 'formatversion' => 2,
            'generator' => 'search', 'gsrsearch' => 'filemime:image/jpeg '.(($options['quality_only'] ?? true) ? 'incategory:Quality_images ' : '').$query, 'gsrnamespace' => 6,
            'gsrlimit' => self::PER_PAGE, 'gsroffset' => ($page - 1) * self::PER_PAGE,
            'prop' => 'imageinfo', 'iiprop' => 'url|size|mime|extmetadata', 'iiurlwidth' => 2560,
            'iiextmetadatafilter' => 'License|LicenseShortName|Artist|ObjectName',
        ])->throw();

        $items = [];
        foreach ($response->json('query.pages', []) as $p) {
            $info = $p['imageinfo'][0] ?? null;
            if (! $info) {
                continue;
            }
            $meta = $info['extmetadata'] ?? [];
            $items[] = [
                'index' => (int) ($p['index'] ?? 0),
                'key' => 'wikimedia:'.($p['pageid'] ?? md5($p['title'] ?? '')),
                'title' => $meta['ObjectName']['value'] ?? preg_replace('/^File:|\.\w+$/', '', $p['title'] ?? ''),
                'width' => (int) ($info['width'] ?? 0),
                'height' => (int) ($info['height'] ?? 0),
                'mime' => $info['mime'] ?? '',
                'url' => $info['thumburl'] ?? $info['url'] ?? null,
                // short link: encoded non-Latin file names overflow the 255-char source_url column
                'page' => isset($p['pageid']) ? 'https://commons.wikimedia.org/?curid='.$p['pageid'] : ($info['descriptionurl'] ?? null),
                'license' => $meta['License']['value'] ?? '',
                'license_name' => $meta['LicenseShortName']['value'] ?? '',
                'artist' => trim(html_entity_decode(strip_tags($meta['Artist']['value'] ?? ''))),
            ];
        }
        // generator results come keyed by pageid in random order; keep search order stable
        usort($items, fn ($a, $b) => $a['index'] <=> $b['index']);

        return ['items' => $items, 'has_more' => $response->json('continue') !== null];
    }

    public function save(array $candidate, ImportRun $run): Wallpaper
    {
        $license = self::licenseFor($candidate['license']);
        if (! $license) {
            throw new ImportSkipped('licence not allowed ('.($candidate['license_name'] ?: 'unknown').')');
        }
        if (! in_array($candidate['mime'], ['image/jpeg', 'image/png', 'image/webp'], true) || ! $candidate['url']) {
            throw new ImportSkipped('not a JPEG/PNG/WebP');
        }
        $minWidth = (int) $run->option('min_width', 1280);
        if (max($candidate['width'], $candidate['height']) < $minWidth) {
            throw new ImportSkipped("too small ({$candidate['width']}×{$candidate['height']})");
        }

        $tmp = tempnam(sys_get_temp_dir(), 'wmc');
        try {
            self::http()->timeout(90)->sink($tmp)->get($candidate['url'])->throw();
            if (filesize($tmp) > self::MAX_BYTES) {
                throw new ImportSkipped('file over 15 MB');
            }
            $info = @getimagesize($tmp);
            $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2] ?? 0] ?? null;
            if (! $ext) {
                throw new ImportSkipped('downloaded file is not an image');
            }
            [$width, $height] = $info;
            $orientation = $this->orientationFor($width, $height);
            if (! $this->wantedOrientationMatches($run, $orientation)) {
                throw new ImportSkipped("not {$run->option('orientation')} ({$width}×{$height})");
            }

            $title = $this->cleanTitle($candidate['title'], $run->query.' wallpaper');
            $path = 'wallpapers/wmc-'.Str::after($candidate['key'], ':').'.'.$ext;
            Storage::disk('public')->put($path, fopen($tmp, 'r'));

            $artist = Str::limit($candidate['artist'] ?: 'Unknown author', 150, '…');

            return Wallpaper::create([
                'title' => $title,
                'slug' => $this->slugFor($title),
                'category' => $run->category,
                'orientation' => $orientation,
                'width' => $width,
                'height' => $height,
                'file_path' => $path,
                'license' => $license,
                'attribution' => Str::limit($artist.' · '.($candidate['license_name'] ?: strtoupper($candidate['license'])).' · via Wikimedia Commons', 250, ''),
                'source_url' => $candidate['page'],
                'source_key' => $candidate['key'],
                'status' => $run->option('status', 'in_review'),
            ]);
        } finally {
            @unlink($tmp);
        }
    }
}
