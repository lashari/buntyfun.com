<?php

namespace App\Services\Automation;

use App\Models\AutomationRun;

interface AutomationTask
{
    /**
     * Do the work, fill $run->items / details / tokens as useful, return a one-line summary.
     * Throw to mark the run failed (the runner handles retries and alerts).
     * Return null to mark it 'skipped' (nothing to do, e.g. budget reached).
     */
    public function handle(AutomationRun $run): ?string;
}
