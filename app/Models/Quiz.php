<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use \App\Models\Concerns\HasContentMeta;

    protected $fillable = ['category_id', 'topic', 'slug', 'title', 'intro', 'difficulty', 'sources', 'outcomes', 'reviewed_at', 'language', 'status', 'published_at'];

    public const DIFFICULTIES = ['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'];

    protected $casts = [
        'published_at' => 'datetime',
        'outcomes' => 'array',
        'reviewed_at' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('position');
    }

    public function results(): HasMany
    {
        return $this->hasMany(QuizResult::class);
    }

    /** Trivia = at least one answer marked right or wrong; otherwise a score-based personality quiz. */
    public function isTrivia(): bool
    {
        return $this->questions->flatMap->answers->contains(fn ($a) => ! is_null($a->is_correct));
    }

    /**
     * Outcome for a score share 0..1. Quiz outcomes: [{min, label, text}], highest min first wins.
     * Trivia quizzes without their own outcomes use generic score bands.
     */
    public function outcomeFor(float $share): ?array
    {
        $list = collect($this->outcomes ?: ($this->isTrivia() ? self::TRIVIA_BANDS : []))->sortByDesc('min');

        return $list->first(fn ($o) => $share >= (float) ($o['min'] ?? 0));
    }

    public function outcomeText(?string $label): ?string
    {
        return collect($this->outcomes ?: self::TRIVIA_BANDS)->firstWhere('label', $label)['text'] ?? null;
    }

    /** Lines of the sources field; URLs are linked by the view. */
    public function sourceLines(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->sources))));
    }

    public const TRIVIA_BANDS = [
        ['min' => 0.9, 'label' => 'Expert', 'text' => 'An excellent score. You clearly know this topic well; try a harder quiz next.'],
        ['min' => 0.6, 'label' => 'Well done', 'text' => 'A strong result with a few slips. The explanations below cover the ones you missed.'],
        ['min' => 0.3, 'label' => 'Good effort', 'text' => 'You got some right. Read the explanations below and try again; most people improve a lot on a second go.'],
        ['min' => 0, 'label' => 'Keep learning', 'text' => 'A tough round. Every answer is explained below, so this is a quick way to learn the topic.'],
    ];
}
