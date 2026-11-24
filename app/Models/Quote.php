<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Quote extends Model
{
    use Concerns\HasContentMeta;

    public const STATUSES = ['in_review' => 'In review', 'published' => 'Published', 'rejected' => 'Rejected'];

    protected $fillable = ['body', 'author', 'author_slug', 'topic', 'tags', 'hash', 'source', 'source_ref', 'status', 'flags'];

    protected $casts = ['flags' => 'array'];

    public static function hashFor(string $body): string
    {
        return sha1(preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($body)));
    }

    public static function slugForAuthor(string $author): string
    {
        return Str::slug($author) ?: 'anonymous';
    }

    public function tagList(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->tags))));
    }

    public function shareText(): string
    {
        return '"'.$this->body.'" — '.$this->author;
    }
}
