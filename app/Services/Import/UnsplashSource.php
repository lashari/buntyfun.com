<?php

namespace App\Services\Import;

use App\Models\ImportRun;
use App\Models\Wallpaper;
use Illuminate\Support\Facades\Http;

/**
 * unsplash.com via the official API (free key). API terms: hotlink the returned
 * image URL (no re-hosting), credit "Photo by <name> on Unsplash" with links, and
 * call download_location when someone downloads (WallpaperController::download).
 */
class UnsplashSource implements ImportSource
{
    use WallpaperFields;

    private const API = 'https://api.unsplash.com';

    public const UTM = 'utm_source=buntyfun&utm_medium=referral';

    public function key(): string
    {
        return 'unsplash';
    }

    public function label(): string
    {
        return 'Unsplash';
    }

    public function website(): string
    {
        return 'https://unsplash.com/';
    }

    public function contentTypes(): array
    {
        return ['wallpaper'];
    }

    public function setupProblem(): ?string
    {
        return config('services.unsplash.access_key')
            ? null
            : 'Add UNSPLASH_ACCESS_KEY to .env (free key from unsplash.com/developers → New Application → Access Key).';
    }

    public function stepSize(): int
    {
        return 10;
    }

    public static function client()
    {
        return Http::timeout(30)->acceptJson()->withHeaders([
            'Authorization' => 'Client-ID '.config('services.unsplash.access_key'),
            'Accept-Version' => 'v1',
        ]);
    }

    public function search(string $query, int $page, array $options): array
    {
        $params = ['query' => $query, 'page' => $page, 'per_page' => 30, 'content_filter' => 'high'];
        $orientation = $options['orientation'] ?? 'any';
        if ($orientation === 'desktop') {
            $params['orientation'] = 'landscape';
        } elseif ($orientation === 'mobile') {
            $params['orientation'] = 'portrait';
        }

        $response = self::client()->get(self::API.'/search/photos', $params)->throw();

        $items = [];
        foreach ($response->json('results', []) as $photo) {
            if (empty($photo['id']) || empty($photo['urls']['raw'])) {
                continue;
            }
            $items[] = [
                'key' => 'unsplash:'.$photo['id'],
                'id' => $photo['id'],
                'title' => $photo['alt_description'] ?: ($photo['description'] ?? null),
                'width' => (int) ($photo['width'] ?? 0),
                'height' => (int) ($photo['height'] ?? 0),
                'raw' => $photo['urls']['raw'],
                'html' => $photo['links']['html'] ?? null,
                'download_location' => $photo['links']['download_location'] ?? null,
                'user_name' => $photo['user']['name'] ?? ($photo['user']['username'] ?? 'Unknown'),
                'user_url' => $photo['user']['links']['html'] ?? null,
            ];
        }

        return ['items' => $items, 'has_more' => $page < (int) $response->json('total_pages', 0)];
    }

    public function save(array $candidate, ImportRun $run): Wallpaper
    {
        $width = $candidate['width'];
        $height = $candidate['height'];
        if ($width < 1 || $height < 1) {
            throw new ImportSkipped('size unknown');
        }
        $minWidth = (int) $run->option('min_width', 1280);
        if (max($width, $height) < $minWidth) {
            throw new ImportSkipped("too small ({$width}×{$height})");
        }
        $orientation = $this->orientationFor($width, $height);
        if (! $this->wantedOrientationMatches($run, $orientation)) {
            throw new ImportSkipped("not {$run->option('orientation')} ({$width}×{$height})");
        }

        // Serve at most 2560px on the long side (imgix params on the raw URL are allowed by the API terms).
        $scale = min(1, 2560 / max($width, $height));
        $sep = str_contains($candidate['raw'], '?') ? '&' : '?';
        $title = $this->cleanTitle($candidate['title'] ?? null, $run->query.' wallpaper');

        return Wallpaper::create([
            'title' => $title,
            'slug' => $this->slugFor($title),
            'category' => $run->category,
            'orientation' => $orientation,
            'width' => (int) round($width * $scale),
            'height' => (int) round($height * $scale),
            'file_path' => '',
            'remote_url' => $candidate['raw'].$sep.'w=2560&fit=max&fm=jpg&q=85',
            'download_location' => $candidate['download_location'],
            'license' => 'licensed',
            'attribution' => 'Photo by '.$candidate['user_name'].' on Unsplash',
            'attribution_url' => $candidate['user_url'] ? $candidate['user_url'].'?'.self::UTM : null,
            'source_url' => $candidate['html'] ? $candidate['html'].'?'.self::UTM : null,
            'source_key' => $candidate['key'],
            'status' => $run->option('status', 'in_review'),
        ]);
    }
}
