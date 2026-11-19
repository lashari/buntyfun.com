<?php

namespace App\Services\Automation\Tasks;

use App\Models\AutomationRun;
use App\Services\Automation\AutomationTask;
use App\Services\Social\SocialPublisher;
use RuntimeException;

class SocialPublishTask implements AutomationTask
{
    public function __construct(private readonly SocialPublisher $publisher) {}

    public function handle(AutomationRun $run): ?string
    {
        [$done, $failed, $errors] = $this->publisher->publishDue();
        $run->items = $done;
        if ($errors) {
            $run->details = ['errors' => $errors];
        }
        if ($done === 0 && $failed === 0) {
            return null;
        }
        if ($done === 0 && $failed > 0) {
            throw new RuntimeException("{$failed} social post(s) failed: ".implode(' | ', $errors));
        }

        return "Published {$done} social post(s)".($failed ? ", {$failed} failed" : '').'.';
    }
}
