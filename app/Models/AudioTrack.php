<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A reviewed, licensed recording: a name pronunciation (kind=name) or a naat (kind=naat).
 * Either an uploaded file (public disk) or an external URL the licence allows us to stream.
 */
class AudioTrack extends Model
{
    public const KINDS = ['name' => 'Name pronunciation', 'naat' => 'Naat recording', 'dua' => 'Dua recording'];

    protected $fillable = ['kind', 'ref_id', 'file_path', 'external_url', 'speaker', 'license', 'source_url', 'status'];

    public function src(): ?string
    {
        if ($this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }

        return $this->external_url;
    }

    public function owner()
    {
        return match ($this->kind) { 'naat' => Naat::find($this->ref_id), 'dua' => Dua::find($this->ref_id), default => NameEntry::find($this->ref_id) };
    }
}
