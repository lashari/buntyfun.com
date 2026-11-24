<?php

namespace App\Services\Library;

use App\Content\TriviaStarter;
use App\Models\Quiz;
use App\Models\TriviaQuestion;

/**
 * Loads the 100 hand-reviewed starter questions (App\Content\TriviaStarter) into the
 * Question Bank as approved and builds their 10 quizzes IN REVIEW. Safe to run twice:
 * a quiz whose slug exists is skipped, and a question already in the bank is reused.
 */
class TriviaStarterLoader
{
    public function __construct(private readonly QuizBuilder $builder) {}

    /** @return array{quizzes:int, questions:int, skipped:int} */
    public function load(?int $reviewerId = null): array
    {
        $stats = ['quizzes' => 0, 'questions' => 0, 'skipped' => 0];
        foreach (TriviaStarter::QUIZZES as $data) {
            if (Quiz::where('slug', $data['slug'])->exists()) {
                $stats['skipped']++;
                continue;
            }
            $rows = collect();
            foreach ($data['questions'] as $i => [$prompt, $correct, $wrong, $explanation, $difficulty]) {
                $hash = TriviaQuestion::hashFor($prompt, $correct);
                $row = TriviaQuestion::firstOrNew(['hash' => $hash]);
                if ($row->exists && $row->quiz_id) {
                    continue;   // already used in another quiz
                }
                $row->fill([
                    'source' => $data['source'], 'source_ref' => $data['source'].':starter:'.$data['slug'].'#'.($i + 1),
                    'topic' => $data['topic'], 'difficulty' => $difficulty, 'prompt' => $prompt, 'correct_answer' => $correct,
                    'wrong_answers' => $wrong, 'explanation' => $explanation, 'status' => 'approved',
                    'reviewed_by' => $reviewerId, 'reviewed_at' => TriviaStarter::REVIEWED,
                ])->save();
                $rows->push($row);
                $stats['questions']++;
            }
            if ($rows->count() >= 5) {
                $this->builder->create(['slug' => $data['slug'], 'title' => $data['title'], 'topic' => $data['topic'], 'intro' => $data['intro']], $rows);
                $stats['quizzes']++;
            }
        }

        return $stats;
    }
}
