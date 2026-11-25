<?php

namespace App\Support;

/**
 * Small JSON-LD builders shared by views (11-25-2026, SEO 10/10 pass).
 * Only facts we hold go in: no ratings, reviews or invented fields.
 */
class Schema
{
    /**
     * ItemList for a hub/list page. $items = iterable of [url, name].
     * Empty list -> [] so the layout prints nothing.
     */
    public static function itemList(string $name, iterable $items, int $startAt = 1): array
    {
        $list = [];
        $pos = $startAt;
        foreach ($items as [$url, $label]) {
            $list[] = ['@type' => 'ListItem', 'position' => $pos++, 'url' => $url, 'name' => $label];
        }

        return $list ? [[
            '@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => $name,
            'numberOfItems' => count($list), 'itemListElement' => $list,
        ]] : [];
    }
}
