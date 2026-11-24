<?php

namespace App\Services\Automation\Tasks;

use App\Models\AutomationRun;
use App\Models\Setting;
use App\Services\Automation\AutomationTask;
use App\Services\Library\QuoteImporter;

class ImportQuotesTask implements AutomationTask
{
    public function __construct(private readonly QuoteImporter $importer) {}

    public function handle(AutomationRun $run): ?string
    {
        $s = $this->importer->importNext((int) (Setting::get('quotes_import_batch') ?? 200));
        if ($s['saved'] === 0 && $s['remaining'] === 0) {
            return null;
        }
        $run->items = $s['saved'];
        $run->details = $s;

        return "Quotes: {$s['saved']} new in review, {$s['duplicates']} duplicates, {$s['rejected']} rejected, {$s['remaining']} left in QuoteSlate.";
    }
}
