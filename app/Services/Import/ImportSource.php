<?php

namespace App\Services\Import;

use App\Models\ImportRun;
use App\Models\Wallpaper;

/**
 * A website we are allowed to pull content from (official API + reuse licence).
 * Sites without such terms (birthdaymsgs.com, wallpapers.com, 4kwallpapers.com)
 * are deliberately not sources: their content belongs to them or to unknown owners.
 */
interface ImportSource
{
    public function key(): string;

    public function label(): string;

    public function website(): string;

    /** @return string[] content types this source can fill, e.g. ['wallpaper'] */
    public function contentTypes(): array;

    /** Null when ready, otherwise what the admin must do first. */
    public function setupProblem(): ?string;

    /** How many candidates one progress step handles (downloads are slow, hotlinks are fast). */
    public function stepSize(): int;

    /**
     * @return array{items: array<int, array>, has_more: bool}
     *               each item has at least: key, title
     */
    public function search(string $query, int $page, array $options): array;

    /** Save one candidate. Throw ImportSkipped for "not suitable", any other exception = failed. */
    public function save(array $candidate, ImportRun $run): Wallpaper;
}
