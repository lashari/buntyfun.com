<?php

namespace App\Services\Library;

use App\Models\DailyFeature;
use App\Models\Game;
use App\Models\Quiz;
use App\Models\Quote;
use App\Models\TriviaQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * What is featured on a given day. An admin-pinned or cron-filled daily_features row wins;
 * otherwise the pick is worked out from the date, so every visitor sees the same item
 * and the page works even if cron never ran.
 */
class DailyPicker
{
    public function item(string $type, ?Carbon $date = null): ?Model
    {
        $date = ($date ?? today())->copy()->startOfDay();
        $model = $this->model($type);
        $row = DailyFeature::whereDate('feature_date', $date)->where('type', $type)->first();
        if ($row && ($item = $model::where('status', 'published')->find($row->item_id))) {
            return $item;
        }

        return $this->auto($type, $date);
    }

    /** Date-based pick that cycles through every published item before repeating. */
    public function auto(string $type, Carbon $date): ?Model
    {
        $model = $this->model($type);
        $ids = $model::where('status', 'published')->orderBy('id')->pluck('id');
        if ($ids->isEmpty()) {
            return null;
        }
        $day = (int) floor($date->copy()->startOfDay()->timestamp / 86400);
        // Fixed shuffle (by id) so consecutive days are not neighbouring ids, then walk it.
        $order = $ids->sortBy(fn ($id) => crc32('bf'.$id))->values();

        return $model::find($order[$day % $order->count()]);
    }

    /** Five approved questions for Daily Trivia: the same five for everyone on that date. */
    public function trivia(?Carbon $date = null, int $count = 5): Collection
    {
        $key = ($date ?? today())->format('Y-m-d');
        $ids = TriviaQuestion::where('status', 'approved')->whereNotNull('explanation')->pluck('id');

        $picked = $ids->sortBy(fn ($id) => crc32($key.'#'.$id))->take($count)->values();

        return TriviaQuestion::whereIn('id', $picked)->get()->sortBy(fn ($q) => $picked->search($q->id))->values();
    }

    /** Cron: fill the next $days days that have no row (or point at an unpublished item). Pinned rows are kept. */
    public function fillAhead(int $days = 7): int
    {
        $made = 0;
        for ($i = 0; $i < $days; $i++) {
            $date = today()->addDays($i);
            foreach (array_keys(DailyFeature::TYPES) as $type) {
                $row = DailyFeature::whereDate('feature_date', $date)->where('type', $type)->first();
                $model = $this->model($type);
                if ($row && ($row->pinned || $model::where('status', 'published')->whereKey($row->item_id)->exists())) {
                    continue;
                }
                $item = $this->auto($type, $date);
                if (! $item) {
                    continue;
                }
                DailyFeature::setFor($date->toDateString(), $type, ['item_id' => $item->id, 'pinned' => false]);
                $made++;
            }
        }

        return $made;
    }

    /** @return class-string<Model> */
    public function model(string $type): string
    {
        return match ($type) {
            'quiz' => Quiz::class,
            'quote' => Quote::class,
            'game' => Game::class,
            'poem' => \App\Models\Poem::class,
            'dua' => \App\Models\Dua::class,
            default => throw new \InvalidArgumentException("Unknown daily type {$type}"),
        };
    }
}
