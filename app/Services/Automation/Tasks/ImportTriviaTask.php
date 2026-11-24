<?php

namespace App\Services\Automation\Tasks;

use App\Content\QuizTopics;
use App\Models\AutomationRun;
use App\Models\Setting;
use App\Services\Automation\AutomationTask;
use App\Services\Library\TriviaImporter;

/** Daily: 2 topics from Open Trivia DB + 1 topic from OpenTriviaQA, rotating. Saves In review only. */
class ImportTriviaTask implements AutomationTask
{
    public function __construct(private readonly TriviaImporter $importer) {}

    public function handle(AutomationRun $run): ?string
    {
        $perTopic = (int) (Setting::get('trivia_import_per_topic') ?? 10);
        $topics = array_keys(QuizTopics::ALL);
        $at = (int) (Setting::get('trivia_import_cursor') ?? 0);
        $totals = ['saved' => 0, 'duplicates' => 0, 'rejected' => 0];
        $lines = [];

        for ($k = 0; $k < 2; $k++) {
            $topic = $topics[($at + $k) % count($topics)];
            if ($k > 0) {
                sleep(6); // Open Trivia DB allows one request per 5 seconds
            }
            $s = $this->importer->fromOpenTdb($topic, $perTopic);
            $lines[] = "opentdb {$topic}: +{$s['saved']}";
            foreach ($totals as $key => $v) {
                $totals[$key] += $s[$key];
            }
        }
        $otqaTopics = array_values(array_filter($topics, fn ($t) => QuizTopics::ALL[$t]['otqa']));
        $topic = $otqaTopics[$at % count($otqaTopics)];
        $s = $this->importer->fromOpenTriviaQa($topic, $perTopic);
        $lines[] = "opentriviaqa {$topic}: +{$s['saved']}";
        foreach ($totals as $key => $v) {
            $totals[$key] += $s[$key];
        }

        Setting::put('trivia_import_cursor', $at + 2);
        $run->items = $totals['saved'];
        $run->details = ['by_source' => $lines] + $totals;

        return "Question Bank: {$totals['saved']} new in review, {$totals['duplicates']} duplicates, {$totals['rejected']} rejected (".implode('; ', $lines).').';
    }
}
