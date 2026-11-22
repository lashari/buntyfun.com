<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Wallpaper extends Model
{
    use \App\Models\Concerns\HasContentMeta;

    protected $fillable = [
        'title', 'slug', 'category', 'orientation', 'width', 'height',
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

    public function resolutionLabel(): string
    {
        return "{$this->width}×{$this->height}";
    }
}
