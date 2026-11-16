<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationRun extends Model
{
    protected $fillable = [
        'job_key', 'triggered_by', 'started_at', 'finished_at', 'status', 'items', 'message', 'details',
        'input_tokens', 'output_tokens', 'cost_usd',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'details' => 'array',
            'cost_usd' => 'float',
        ];
    }
}
