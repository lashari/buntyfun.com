<?php

namespace App\Services\Ads;

use App\Models\Setting;

/**
 * Admin → Ads: where ads may appear. Reads the saved settings once per request.
 * Only decides placement; it never invents ad ids (those come from .env, see config/services.php).
 */
class AdControls
{
    /** Section key => [label, route-name prefixes]. A page outside every section is "other". */
    public const SECTIONS = [
        'home' => ['Home page', ['home']],
        'games' => ['Games', ['games.']],
        'quizzes' => ['Quizzes', ['quizzes.']],
        'daily' => ['Daily page', ['daily']],
        'birthday' => ['Birthday wishes', ['birthday-messages.']],
        'messages' => ['Messages + New Year wishes', ['messages.']],
        'names' => ['Islamic names', ['name-entries.']],
        'quran' => ['Quran', ['quran.']],
        'islamic' => ['Duas, naats, 99 Names, Islamic calendar', ['duas.', 'naats.', 'islamic.']],
        'poetry' => ['Poetry', ['poetry.']],
        'urdu' => ['Urdu corner', ['urdu-content.']],
        'quotes' => ['Quotes', ['quotes.']],
        'wallpapers' => ['Wallpapers', ['wallpapers.']],
        'ai' => ['AI Fun tools', ['ai-tools.']],
        'other' => ['Other pages', []],
    ];

    public const MAX_UNITS = 3;

    /** @return array{enabled: bool, sections: array<string,bool>, desktop: bool, mobile: bool, max_units: int, excluded: list<string>} */
    public static function settings(): array
    {
        $req = request();
        if ($req->attributes->has('ad_controls')) {
            return $req->attributes->get('ad_controls');
        }
        $saved = (array) (Setting::get('ads_sections') ?? []);
        $s = [
            'enabled' => (bool) Setting::get('ads_enabled'),
            'sections' => collect(Setting::DEFAULTS['ads_sections'])->map(fn ($on, $k) => (bool) ($saved[$k] ?? $on))->all(),
            'desktop' => (bool) Setting::get('ads_desktop'),
            'mobile' => (bool) Setting::get('ads_mobile'),
            'max_units' => max(0, min(self::MAX_UNITS, (int) Setting::get('ads_max_units'))),
            'excluded' => array_values((array) (Setting::get('ads_excluded_paths') ?? [])),
        ];
        $req->attributes->set('ad_controls', $s);

        return $s;
    }

    public static function sectionFor(?string $routeName): string
    {
        foreach (self::SECTIONS as $key => [, $prefixes]) {
            foreach ($prefixes as $p) {
                if ($routeName === rtrim($p, '.') || ($routeName && str_ends_with($p, '.') && str_starts_with($routeName, $p))) {
                    return $key;
                }
            }
        }

        return 'other';
    }

    /** "/games/ludo" blocks /games/ludo and /games/ludo/…, not /games/ludo-2. */
    public static function pathExcluded(string $path, array $excluded): bool
    {
        $path = '/'.trim($path, '/');
        foreach ($excluded as $rule) {
            $rule = '/'.trim($rule, '/');
            if ($rule === '/' ? $path === '/' : ($path === $rule || str_starts_with($path, $rule.'/'))) {
                return true;
            }
        }

        return false;
    }

    /** May this page load the ad script at all? (The page itself and noindex are checked in the layout.) */
    public static function allowedHere(): bool
    {
        $s = self::settings();
        if (! $s['enabled'] || (! $s['desktop'] && ! $s['mobile'])) {
            return false;
        }
        if (! ($s['sections'][self::sectionFor(request()->route()?->getName())] ?? false)) {
            return false;
        }

        return ! self::pathExcluded(request()->path(), $s['excluded']);
    }

    /** Counts manual ad units on this page; false once the per-page limit is reached. */
    public static function takeUnit(): bool
    {
        $req = request();
        $used = (int) $req->attributes->get('ad_units_used', 0);
        if ($used >= self::settings()['max_units']) {
            return false;
        }
        $req->attributes->set('ad_units_used', $used + 1);

        return true;
    }

    /** Tailwind classes that hide a manual unit on the device types that are off. */
    public static function deviceClass(): string
    {
        $s = self::settings();

        return match (true) {
            $s['desktop'] && ! $s['mobile'] => 'hidden md:block',
            $s['mobile'] && ! $s['desktop'] => 'md:hidden',
            default => '',
        };
    }
}
