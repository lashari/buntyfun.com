<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiTool extends Model
{
    protected $fillable = ['slug', 'title', 'description', 'prompt_template', 'status'];

    public function usages(): HasMany
    {
        return $this->hasMany(AiUsage::class);
    }

    public function buildPrompt(string $input): string
    {
        return str_replace('{input}', $input, $this->prompt_template);
    }
}
