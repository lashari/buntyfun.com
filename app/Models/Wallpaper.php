<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Wallpaper extends Model
{
    use \App\Models\Concerns\HasContentMeta;

    protected $fillable = [
        'title', 'description', 'slug', 'category', 'orientation', 'width', 'height',
        'file_path', 'license', 'attribution', 'source_url', 'status',
        'source_key', 'remote_url', 'download_location', 'attribution_url',
    ];

    public function url(): string
    {
        // Unsplash photos are hotlinked (their API terms), everything else is stored here.
        if ($this->remote_url) {
            return $this->remote_url;
        }

        return Storage::disk('public')->url($this->file_path);
    }

    /** 640 px copy for grids; falls back to the full image until it exists. */
    public function thumbUrl(): string
    {
        return $this->smallCopy(\App\Services\Import\ThumbnailMaker::thumbPath($this));
    }

    /** 1600 px copy for the wallpaper page / share image. */
    public function previewUrl(): string
    {
        return $this->smallCopy(\App\Services\Import\ThumbnailMaker::previewPath($this));
    }

    private function smallCopy(string $path): string
    {
        if ($this->remote_url) {
            return $this->remote_url;
        }

        return Storage::disk('public')->exists($path)
            ? Storage::disk('public')->url($path).'?v='.$this->updated_at?->timestamp
            : $this->url();
    }

    /** A single wallpaper page is worth indexing only with a real caption of this many words. */
    public const MIN_DESCRIPTION_WORDS = 20;

    public function hasUsefulDescription(): bool
    {
        return str_word_count((string) $this->description) >= self::MIN_DESCRIPTION_WORDS;
    }

    /** Where the caption came from, for the credit line under it. */
    public function descriptionSource(): ?string
    {
        return match (strtok((string) $this->source_key, ':')) {
            'nasa' => 'NASA Image Library',
            'wikimedia' => 'Wikimedia Commons',
            default => null,
        };
    }

    public function resolutionLabel(): string
    {
        return "{$this->width}×{$this->height}";
    }
}
