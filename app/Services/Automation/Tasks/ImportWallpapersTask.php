<?php

namespace App\Services\Automation\Tasks;

use App\Models\AutomationRun;
use App\Models\ImportRun;
use App\Models\Setting;
use App\Services\Automation\AutomationTask;
use App\Services\Import\ImportRunner;

/**
 * Weekly: runs the next saved wallpaper search through the existing importer
 * (Wikimedia Commons quality images / NASA). Saves In review only.
 * Pexels, Pixabay and Unsplash are not used: their terms forbid wallpaper sites.
 */
class ImportWallpapersTask implements AutomationTask
{
    public const DEFAULT_SEARCHES = [
        ['source' => 'wikimedia', 'query' => 'mountain landscape', 'category' => 'landscapes'],
        ['source' => 'nasa', 'query' => 'nebula', 'category' => 'space'],
        ['source' => 'wikimedia', 'query' => 'mosque architecture', 'category' => 'islamic'],
        ['source' => 'wikimedia', 'query' => 'flowers macro', 'category' => 'nature'],
        ['source' => 'wikimedia', 'query' => 'wild animals', 'category' => 'animals'],
        ['source' => 'nasa', 'query' => 'earth from space', 'category' => 'space'],
    ];

    public function __construct(private readonly ImportRunner $runner) {}

    public function handle(AutomationRun $run): ?string
    {
        $searches = Setting::get('wallpaper_searches') ?: self::DEFAULT_SEARCHES;
        $at = (int) (Setting::get('wallpaper_search_cursor') ?? 0);
        $s = $searches[$at % count($searches)];
        Setting::put('wallpaper_search_cursor', $at + 1);

        $import = ImportRun::create([
            'user_id' => null, 'content_type' => 'wallpaper', 'source' => $s['source'], 'query' => $s['query'],
            'category' => $s['category'], 'target_count' => (int) ($s['count'] ?? 10), 'page' => 1,
            'options' => ['orientation' => 'any', 'min_width' => 1920, 'quality_only' => true, 'status' => 'in_review'],
        ]);
        $import = $this->runner->start($import);
        $until = microtime(true) + 240;
        while ($import->status === 'running' && microtime(true) < $until) {
            $import = $this->runner->step($import);
        }
        $run->items = $import->saved;
        $run->details = ['import_run' => $import->id, 'search' => $s];

        return "Wallpapers \"{$s['query']}\" → {$s['category']}: {$import->saved} saved in review, {$import->skipped} skipped (import run #{$import->id}).";
    }
}
