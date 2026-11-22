<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportRun extends Model
{
    protected $fillable = [
        'user_id', 'content_type', 'source', 'query', 'category', 'target_count', 'page',
        'options', 'candidates', 'cursor', 'saved', 'skipped', 'failed', 'status', 'message',
    ];

    protected $casts = ['options' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ImportRunItem::class);
    }

    public function candidateList(): array
    {
        return json_decode($this->candidates ?? '[]', true) ?: [];
    }

    public function option(string $key, mixed $default = null): mixed
    {
        return ($this->options ?? [])[$key] ?? $default;
    }

    /** Wallpapers this run saved that still exist. */
    public function savedWallpapers()
    {
        $ids = $this->items()->where('status', 'saved')->where('item_type', 'wallpaper')->pluck('item_id');

        return Wallpaper::whereIn('id', $ids);
    }

    public function progress(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'saved' => $this->saved,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
            'target' => $this->target_count,
            'checked' => $this->cursor,
            'found' => count($this->candidateList()),
            'message' => $this->message,
        ];
    }
}
