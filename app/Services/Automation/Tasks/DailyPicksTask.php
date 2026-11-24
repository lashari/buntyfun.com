<?php

namespace App\Services\Automation\Tasks;

use App\Models\AutomationRun;
use App\Services\Automation\AutomationTask;
use App\Services\Library\DailyPicker;

class DailyPicksTask implements AutomationTask
{
    public function __construct(private readonly DailyPicker $picker) {}

    public function handle(AutomationRun $run): ?string
    {
        $made = $this->picker->fillAhead(7);
        $run->items = $made;

        return $made ? "Daily Schedule: {$made} slot(s) filled for the next 7 days." : null;
    }
}
