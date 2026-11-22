<?php

namespace App\Services\Import;

use App\Models\ImportRun;
use Illuminate\Support\Str;

trait WallpaperFields
{
    protected function orientationFor(int $width, int $height): string
    {
        if ($width >= $height * 1.2) {
            return 'desktop';
        }

        return $height >= $width * 1.2 ? 'mobile' : 'both';
    }

    protected function cleanTitle(?string $title, string $fallback): string
    {
        $title = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $title)));
        if ($title === '') {
            $title = $fallback;
        }

        return Str::limit(Str::ucfirst($title), 120, '');
    }

    protected function slugFor(string $title): string
    {
        return Str::slug(Str::limit($title, 60, '')).'-'.Str::lower(Str::random(6));
    }

    protected function wantedOrientationMatches(ImportRun $run, string $orientation): bool
    {
        $want = $run->option('orientation', 'any');

        return $want === 'any' || $orientation === 'both' || $orientation === $want;
    }
}
