<?php

namespace App\Services\Automation\Tasks;

use App\Models\AuditLog;
use App\Models\AutomationRun;
use App\Models\Poem;
use App\Models\Poet;
use App\Services\Automation\AutomationTask;

/**
 * Publishes 10 poems a day (round-robin over poets, oldest In review first, 4–40 lines).
 * Off by default: turn it on in Admin → Automation once you are happy with the imported texts.
 */
class PublishPoemsTask implements AutomationTask
{
    public const PER_DAY = 10;

    public function handle(AutomationRun $run): ?string
    {
        $done = 0;
        $poets = Poet::where('status', 'published')->pluck('id')->shuffle();
        while ($done < self::PER_DAY) {
            $before = $done;
            foreach ($poets as $pid) {
                $poem = Poem::where('poet_id', $pid)->where('status', 'in_review')->whereBetween('line_count', [4, 40])->orderBy('id')->first();
                if ($poem) {
                    $poem->update(['status' => 'published']);
                    AuditLog::recordSystem('published', 'poem', $poem->id);
                    if (++$done >= self::PER_DAY) {
                        break;
                    }
                }
            }
            if ($done === $before) {
                break;
            }
        }
        $run->items = $done;

        return $done ? "Published {$done} poem(s). They appear in the sitemap at once." : null;
    }
}
