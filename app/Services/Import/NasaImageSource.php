<?php

namespace App\Services\Import;

use App\Models\ImportRun;
use App\Models\Wallpaper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * images.nasa.gov — official API, no key. NASA imagery is generally not
 * copyrighted (public domain); some items are partner-supplied, so the credit
 * line (center / photographer) is always stored and shown.
 */
class NasaImageSource implements ImportSource
{
    use WallpaperFields;

    private const API = 'https://images-api.nasa.gov';

    private const MAX_BYTES = 15 * 1024 * 1024;

    public function key(): string
    {
        return 'nasa';
    }

    public function label(): string
    {
        return 'NASA Image Library';
    }

    public function website(): string
    {
        return 'https://images.nasa.gov/';
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
        return 2;
    }

    public function search(string $query, int $page, array $options): array
    {
        $response = Http::timeout(30)->acceptJson()->get(self::API.'/search', [
            'q' => $query,
            'media_type' => 'image',
            'page' => $page,
            'page_size' => 100,
        ])->throw();

        $items = [];
        foreach ($response->json('collection.items', []) as $item) {
            $data = $item['data'][0] ?? [];
            if (empty($data['nasa_id'])) {
                continue;
            }
            $items[] = [
                'key' => 'nasa:'.$data['nasa_id'],
                'id' => $data['nasa_id'],
                'title' => $data['title'] ?? $data['nasa_id'],
                'center' => $data['center'] ?? null,
                'photographer' => $data['photographer'] ?? ($data['secondary_creator'] ?? null),
                'description' => $data['description'] ?? ($data['description_508'] ?? null),
                'thumb' => $item['links'][0]['href'] ?? null,
            ];
        }

        $hasNext = collect($response->json('collection.links', []))->contains(fn ($l) => ($l['rel'] ?? '') === 'next');

        return ['items' => $items, 'has_more' => $hasNext];
    }

    public function save(array $candidate, ImportRun $run): Wallpaper
    {
        $assets = Http::timeout(30)->acceptJson()
            ->get(self::API.'/asset/'.rawurlencode($candidate['id']))->throw()
            ->json('collection.items', []);
        $urls = array_map(fn ($a) => preg_replace('#^http://#', 'https://', $a['href'] ?? ''), $assets);

        $tmp = tempnam(sys_get_temp_dir(), 'nasa');
        try {
            $info = null;
            foreach (['~orig.jpg', '~large.jpg'] as $suffix) {
                $url = collect($urls)->first(fn ($u) => str_ends_with(strtolower($u), $suffix));
                if (! $url) {
                    continue;
                }
                $head = Http::timeout(20)->head($url);
                if ((int) $head->header('Content-Length') > self::MAX_BYTES) {
                    continue; // too big for shared hosting, try the smaller size
                }
                Http::timeout(90)->sink($tmp)->get($url)->throw();
                if (filesize($tmp) > self::MAX_BYTES) {
                    continue;
                }
                $info = @getimagesize($tmp);
                if ($info && ($info[2] ?? null) === IMAGETYPE_JPEG) {
                    break;
                }
                $info = null;
            }
            if (! $info) {
                throw new ImportSkipped('no JPEG under 15 MB');
            }

            [$width, $height] = $info;
            $minWidth = (int) $run->option('min_width', 1280);
            if (max($width, $height) < $minWidth) {
                throw new ImportSkipped("too small ({$width}×{$height})");
            }
            $orientation = $this->orientationFor($width, $height);
            if (! $this->wantedOrientationMatches($run, $orientation)) {
                throw new ImportSkipped("not {$run->option('orientation')} ({$width}×{$height})");
            }

            $title = $this->cleanTitle($candidate['title'] ?? null, $run->query.' NASA image');
            $path = 'wallpapers/nasa-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $candidate['id']).'.jpg';
            Storage::disk('public')->put($path, fopen($tmp, 'r'));

            // NASA's own credit line when given (often "NASA/JPL-Caltech"), else NASA + center.
            $credit = trim((string) ($candidate['photographer'] ?? ''))
                ?: collect(['NASA', $candidate['center'] ?? null])->filter()->implode(' / ');

            return Wallpaper::create([
                'title' => $title,
                'description' => WallpaperDescriptions::clean($candidate['description'] ?? null, $title),
                'slug' => $this->slugFor($title),
                'category' => $run->category,
                'orientation' => $orientation,
                'width' => $width,
                'height' => $height,
                'file_path' => $path,
                'license' => 'public_domain',
                'attribution' => 'Image: '.$credit,
                'source_url' => 'https://images.nasa.gov/details/'.rawurlencode($candidate['id']),
                'source_key' => $candidate['key'],
                'status' => $run->option('status', 'in_review'),
            ]);
        } finally {
            @unlink($tmp);
        }
    }
}
