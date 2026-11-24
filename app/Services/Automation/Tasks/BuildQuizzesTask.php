<?php

namespace App\Services\Automation\Tasks;

use App\Models\AutomationRun;
use App\Services\Automation\AutomationTask;
use App\Services\Library\QuizBuilder;

class BuildQuizzesTask implements AutomationTask
{
    public function __construct(private readonly QuizBuilder $builder) {}

    public function handle(AutomationRun $run): ?string
    {
        $made = $this->builder->buildAll(5);
        if (! $made) {
            return null;
        }
        $run->items = count($made);
        $run->details = ['quizzes' => array_map(fn ($q) => $q->title, $made)];

        return count($made).' new quiz(zes) waiting in Admin → Quizzes (In review).';
    }
}
