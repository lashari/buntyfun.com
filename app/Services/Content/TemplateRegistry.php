<?php

namespace App\Services\Content;

use App\Services\Content\Templates\BirthdayTemplate;
use App\Services\Content\Templates\ContentTemplate;
use App\Services\Content\Templates\MessageTemplate;
use App\Services\Content\Templates\NameTemplate;
use App\Services\Content\Templates\UrduTemplate;
use InvalidArgumentException;

class TemplateRegistry
{
    private const MAP = [
        'birthday' => BirthdayTemplate::class,
        'message' => MessageTemplate::class,
        'urdu' => UrduTemplate::class,
        'name' => NameTemplate::class,
    ];

    public static function get(string $type): ContentTemplate
    {
        $class = self::MAP[$type] ?? throw new InvalidArgumentException("Unknown content type {$type}");

        return app($class);
    }

    /** @return array<string, ContentTemplate> */
    public static function all(): array
    {
        return array_map(fn ($c) => app($c), self::MAP);
    }
}
