<?php

namespace App\Services\Automation\Tasks;

use App\Models\AutomationRun;
use App\Services\Automation\AutomationTask;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class BrokenLinksTask implements AutomationTask
{
    private const MAX_URLS = 400;

    public function handle(AutomationRun $run): ?string
    {
        $sitemap = Http::timeout(30)->get(route('sitemap'));
        if (! $sitemap->ok()) {
            throw new RuntimeException('Sitemap returned HTTP '.$sitemap->status());
        }
        preg_match_all('~<loc>([^<]+)</loc>~', $sitemap->body(), $m);
        $urls = array_slice(array_unique(array_map('html_entity_decode', $m[1])), 0, self::MAX_URLS);

        $broken = [];
        foreach (array_chunk($urls, 10) as $chunk) {
            $responses = Http::pool(fn ($pool) => array_map(fn ($u) => $pool->as($u)->timeout(20)->withoutRedirecting()->get($u), $chunk));
            foreach ($responses as $url => $r) {
                $code = $r instanceof Throwable ? 0 : $r->status();
                if ($code !== 200) {
                    $broken[] = ['url' => $url, 'status' => $code];
                }
            }
        }

        $run->items = count($urls);
        $run->details = ['checked' => count($urls), 'broken' => $broken];

        return count($broken) ? count($broken).' of '.count($urls).' sitemap URLs not OK (see details).' : 'All '.count($urls).' sitemap URLs return 200.';
    }
}
