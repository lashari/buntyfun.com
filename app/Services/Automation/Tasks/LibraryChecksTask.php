<?php

namespace App\Services\Automation\Tasks;

use App\Models\AllahName;
use App\Models\AutomationRun;
use App\Models\Dua;
use App\Models\NameEntry;
use App\Models\Poem;
use App\Services\Automation\AutomationTask;
use Illuminate\Support\Facades\DB;

/** Daily report on the library: duplicates and missing data. Changes nothing. */
class LibraryChecksTask implements AutomationTask
{
    public function handle(AutomationRun $run): ?string
    {
        $issues = [];
        $dupNames = NameEntry::select(DB::raw('LOWER(name_en) n'), DB::raw('count(*) c'))->groupBy('n')->having('c', '>', 1)->pluck('c', 'n');
        foreach ($dupNames as $n => $c) {
            $issues[] = "Name \"{$n}\" appears {$c} times";
        }
        NameEntry::where('status', 'published')->where(fn ($q) => $q->whereNull('name_ar')->orWhere('name_ar', '')->orWhereNull('source_ref')->orWhere('source_ref', ''))
            ->pluck('name_en')->each(function ($n) use (&$issues) { $issues[] = "Published name {$n} has no Arabic spelling or source"; });
        $dupBodies = Poem::select('title', DB::raw('count(*) c'))->groupBy('title', 'poet_id')->having('c', '>', 1)->pluck('c', 'title');
        foreach ($dupBodies as $t => $c) {
            $issues[] = "Poem title \"".mb_substr($t, 0, 40)."\" used {$c} times by one poet (check for repeats)";
        }
        Poem::where('status', 'published')->where(fn ($q) => $q->where('line_count', '<', 2)->orWhere('line_count', '>', 120))
            ->pluck('slug')->each(function ($s) use (&$issues) { $issues[] = "Published poem {$s} is very short or very long"; });
        Dua::where('status', 'published')->where(fn ($q) => $q->whereNull('reference')->orWhere('reference', ''))
            ->pluck('slug')->each(function ($s) use (&$issues) { $issues[] = "Published dua {$s} has no reference"; });
        $allah = AllahName::count();
        if ($allah && $allah !== 99) {
            $issues[] = "99 Names table has {$allah} rows";
        }
        $run->items = count($issues);
        $run->details = ['issues' => array_slice($issues, 0, 200)];

        return $issues ? count($issues).' issue(s) found: '.implode('; ', array_slice($issues, 0, 5)).(count($issues) > 5 ? '…' : '') : 'Library checks: no problems found.';
    }
}
