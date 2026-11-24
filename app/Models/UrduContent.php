<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrduContent extends Model
{
    use \App\Models\Concerns\HasContentMeta;

    protected $fillable = ['category', 'body', 'slug', 'status'];

    /** Topic page (/urdu-quotes …) when one exists, else the filtered list. */
    public static function categoryUrl(string $category): string
    {
        $page = \App\Content\UrduPages::forCategory($category);

        return $page ? route('urdu-content.page', $page) : route('urdu-content.index', ['category' => $category]);
    }
}
