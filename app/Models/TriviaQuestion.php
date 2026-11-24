<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Question Bank row. Imports land here as in_review; only approved rows go into quizzes or Daily Trivia. */
class TriviaQuestion extends Model
{
    public const STATUSES = ['in_review' => 'In review', 'approved' => 'Approved', 'rejected' => 'Rejected'];

    protected $fillable = ['source', 'source_ref', 'hash', 'topic', 'difficulty', 'prompt', 'correct_answer', 'wrong_answers',
        'explanation', 'status', 'flags', 'quiz_id', 'reviewed_by', 'reviewed_at'];

    protected $casts = [
        'wrong_answers' => 'array',
        'flags' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /** Same normalisation for every source, so one question imported twice is caught. */
    public static function hashFor(string $prompt, string $correct): string
    {
        $norm = fn (string $s) => preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($s));

        return sha1($norm($prompt).'|'.$norm($correct));
    }

    /** [[label, isCorrect]] in the same fair order quizzes use. */
    public function answerPairs(): array
    {
        $pairs = [[$this->correct_answer, true]];
        foreach ((array) $this->wrong_answers as $w) {
            $pairs[] = [$w, false];
        }

        return \App\Content\QuizLibrary::orderedAnswers($this->prompt, $pairs);
    }
}
