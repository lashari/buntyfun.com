<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AutomationJob extends Model
{
    protected $fillable = [
        'key', 'name', 'description', 'enabled', 'frequency', 'run_at', 'max_retries',
        'consecutive_failures', 'last_run_at', 'last_status', 'next_run_at',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'last_run_at' => 'datetime',
            'next_run_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    /** Next normal slot after $from (retries use their own backoff, see AutomationRunner). */
    public function nextSlotAfter(Carbon $from): Carbon
    {
        $from = $from->copy()->second(0);
        [$h, $m] = array_map('intval', explode(':', $this->run_at ?: '00:00') + [0, 0]);

        return match ($this->frequency) {
            'every_5_minutes' => $from->copy()->addMinutes(5 - ($from->minute % 5)),
            'hourly' => $from->copy()->addHour()->minute(0),
            'daily' => ($t = $from->copy()->setTime($h, $m))->gt($from) ? $t : $t->addDay(),
            'weekly' => ($t = $from->copy()->startOfWeek()->setTime($h, $m))->gt($from) ? $t : $t->addWeek(),
        };
    }

    public function isDue(Carbon $now): bool
    {
        return $this->enabled && ($this->next_run_at === null || $this->next_run_at->lte($now));
    }
}
