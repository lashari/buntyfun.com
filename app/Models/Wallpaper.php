<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Wallpaper extends Model
{
    protected $fillable = [
        'title', 'slug', 'category', 'orientation', 'width', 'height',
        'file_path', 'license', 'attribution', 'source_url', 'status',
    ];

    public function url(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function resolutionLabel(): string
    {
        return "{$this->width}×{$this->height}";
    }
}
