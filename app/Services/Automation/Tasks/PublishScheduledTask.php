<?php

namespace App\Services\Automation\Tasks;

use App\Models\AutomationRun;
use App\Services\Automation\AutomationTask;
use App\Services\Content\ScheduledPublisher;

class PublishScheduledTask implements AutomationTask
{
    public function __construct(private readonly ScheduledPublisher $publisher) {}

    public function handle(AutomationRun $run): ?string
    {
        [$done, $skipped] = $this->publisher->run();
        $run->items = $done;
        if ($skipped) {
            $run->details = ['skipped' => $skipped];
        }

        if ($done === 0 && ! $skipped) {
            return null;
        }

        return "Published {$done} scheduled item(s)".($skipped ? ', skipped '.count($skipped) : '').'. Sitemap and RSS update live.';
    }
}
