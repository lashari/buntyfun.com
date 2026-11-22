<?php

namespace App\Services\Import;

use App\Models\ImportRun;
use App\Models\Wallpaper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Works an import in small steps. The admin page calls step() again and again
 * (no queue worker / no long request on shared hosting), so each call stays well
 * inside PHP's time limit and the page can show live progress.
 */
class ImportRunner
{
    /** Extra result pages fetched automatically when duplicates/too-small eat the first page. */
    private const MAX_PAGES = 5;

    private const STEP_SECONDS = 20;

    /** @return array<string, ImportSource> */
    public static function sources(): array
    {
        // Unsplash (and Pexels/Pixabay) licences forbid wallpaper sites, so Unsplash is
        // switched off here; UnsplashSource stays for any already-imported rows.
        return collect([new NasaImageSource, new WikimediaSource])
            ->keyBy(fn (ImportSource $s) => $s->key())->all();
    }

    public static function source(string $key): ImportSource
    {
        return self::sources()[$key] ?? throw new \InvalidArgumentException("Unknown source {$key}");
    }

    /** Fetch the first page of candidates. */
    public function start(ImportRun $run): ImportRun
    {
        $run->refresh(); // pick up DB defaults (page, cursor, counters)
        $source = self::source($run->source);
        try {
            $result = $source->search($run->query, $run->page, $run->options ?? []);
            $run->update([
                'candidates' => json_encode($result['items']),
                'options' => [...($run->options ?? []), 'has_more' => $result['has_more'], 'first_page' => $run->page],
                'message' => count($result['items']).' found on '.$source->label().' page '.$run->page,
            ]);
            if ($result['items'] === []) {
                $run->update(['status' => 'done', 'message' => 'Nothing found for "'.$run->query.'".']);
            }
        } catch (\Throwable $e) {
            Log::warning('import start failed', ['run' => $run->id, 'error' => $e->getMessage()]);
            $run->update(['status' => 'failed', 'message' => 'Search failed: '.mb_substr($e->getMessage(), 0, 300)]);
        }

        return $run->fresh();
    }

    public function step(ImportRun $run): ImportRun
    {
        if ($run->status !== 'running') {
            return $run;
        }
        $lock = Cache::lock('import-run-'.$run->id, 120);
        if (! $lock->get()) {
            return $run->fresh(); // another tab is working this run
        }

        try {
            $run->refresh();
            $source = self::source($run->source);
            $started = microtime(true);
            $done = 0;

            while ($run->status === 'running' && $done < $source->stepSize()
                && microtime(true) - $started < self::STEP_SECONDS) {
                if ($run->saved >= $run->target_count) {
                    $run->update(['status' => 'done', 'message' => 'Finished: target reached.']);
                    break;
                }
                $candidates = $run->candidateList();
                if ($run->cursor >= count($candidates)) {
                    if (! $this->loadNextPage($run, $source)) {
                        $run->update(['status' => 'done', 'message' => 'Finished: no more results from '.$source->label().'.']);
                    }

                    continue;
                }

                $this->handle($run, $source, $candidates[$run->cursor]);
                $run->increment('cursor');
                $done++;
            }

            if ($run->status === 'running' && $run->saved >= $run->target_count) {
                $run->update(['status' => 'done', 'message' => 'Finished: target reached.']);
            }
        } finally {
            $lock->release();
        }

        return $run->fresh();
    }

    private function handle(ImportRun $run, ImportSource $source, array $candidate): void
    {
        $item = ['source_key' => $candidate['key'], 'title' => mb_substr((string) ($candidate['title'] ?? ''), 0, 250)];

        if (Wallpaper::where('source_key', $candidate['key'])->exists()) {
            $run->items()->create([...$item, 'status' => 'skipped', 'message' => 'already imported']);
            $run->increment('skipped');

            return;
        }

        try {
            $wallpaper = $source->save($candidate, $run);
            app(ThumbnailMaker::class)->make($wallpaper); // best effort; backfill retries later
            $run->items()->create([...$item, 'status' => 'saved', 'item_type' => 'wallpaper', 'item_id' => $wallpaper->id]);
            $run->increment('saved');
        } catch (ImportSkipped $e) {
            $run->items()->create([...$item, 'status' => 'skipped', 'message' => $e->getMessage()]);
            $run->increment('skipped');
        } catch (\Throwable $e) {
            Log::warning('import item failed', ['run' => $run->id, 'key' => $candidate['key'], 'error' => $e->getMessage()]);
            $run->items()->create([...$item, 'status' => 'failed', 'message' => mb_substr($e->getMessage(), 0, 290)]);
            $run->increment('failed');
        }
    }

    private function loadNextPage(ImportRun $run, ImportSource $source): bool
    {
        $firstPage = (int) $run->option('first_page', 1);
        if (! $run->option('has_more') || $run->page >= $firstPage + self::MAX_PAGES - 1) {
            return false;
        }

        try {
            $page = $run->page + 1;
            $result = $source->search($run->query, $page, $run->options ?? []);
        } catch (\Throwable $e) {
            $run->update(['message' => 'Next page failed: '.mb_substr($e->getMessage(), 0, 300)]);

            return false;
        }
        if ($result['items'] === []) {
            return false;
        }

        $run->update([
            'page' => $page,
            'candidates' => json_encode([...$run->candidateList(), ...$result['items']]),
            'options' => [...($run->options ?? []), 'has_more' => $result['has_more']],
        ]);

        return true;
    }
}
