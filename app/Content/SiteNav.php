<?php

namespace App\Content;

use App\Models\Message;
use App\Models\Wallpaper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * One definition of the site's sections, used by the header (dropdowns), the mobile menu,
 * the footer and the "Explore" blocks on hub pages, so every place links the same URLs.
 * Every entry is an existing route: no new URLs were added for navigation (11-25-2026).
 */
class SiteNav
{
    /**
     * @return array<string, array{label: string, url: string, intro: string, links: array<int, array{0: string, 1: string}>}>
     */
    public static function groups(): array
    {
        $live = Cache::remember('layout.nav-live-v2', 600, fn () => [
            'quran' => Schema::hasTable('quran_surahs') && \App\Models\QuranSurah::count() === 114,
            'naats' => Schema::hasTable('naats') && \App\Models\Naat::where('status', 'published')->exists(),
            'messages' => Message::where('status', 'published')->exists(),
            'quotes' => Schema::hasTable('quotes') && \App\Models\Quote::where('status', 'published')->exists(),
            // biggest categories first; only categories with published wallpapers
            'wallpapers' => Wallpaper::where('status', 'published')->selectRaw('category, count(*) c')
                ->groupBy('category')->orderByDesc('c')->orderBy('category')->limit(8)->pluck('category')->all(),
            // same rule as the sitemap: topics with 2+ quizzes are indexable
            'quizTopics' => array_keys(array_filter(\App\Http\Controllers\QuizController::topicCounts(), fn ($c) => $c >= 2)),
            'games' => self::popularGames(5)->pluck('title', 'slug')->all(),
            'ai' => \App\Models\AiTool::where('status', 'published')->orderBy('id')->limit(6)->pluck('title', 'slug')->all(),
        ]);

        $groups = [
            'games' => [
                'label' => 'Games', 'url' => route('games.index'),
                'intro' => 'Free browser games: puzzles, board games, word games and quick reflex tests.',
                'links' => array_merge([
                    [route('games.index'), 'All games'],
                    [route('daily'), 'Daily Challenge'],
                ],
                    // ?for= filters are left out: they canonicalise to /games, so header links go to real game pages
                    array_map(fn ($slug, $title) => [route('games.show', $slug), $title], array_keys($live['games']), $live['games'])
                ),
            ],
            'quizzes' => [
                'label' => 'Quizzes', 'url' => route('quizzes.index'),
                'intro' => 'Short trivia quizzes with every answer explained.',
                'links' => array_merge(
                    [[route('quizzes.index'), 'All quizzes'], [route('daily'), 'Daily quiz']],
                    array_map(fn ($t) => [route('quizzes.topic', $t), QuizTopics::ALL[$t]['label'].' quizzes'],
                        array_slice($live['quizTopics'], 0, 6))
                ),
            ],
            'wishes' => [
                'label' => 'Wishes & Messages', 'url' => $live['messages'] ? route('messages.index') : route('birthday-messages.index'),
                'intro' => 'Birthday wishes and messages for every occasion, ready to copy or send on WhatsApp.',
                'links' => array_values(array_filter([
                    [route('birthday-messages.index'), 'Birthday wishes'],
                    [route('birthday-messages.urdu'), 'Urdu birthday wishes'],
                    [route('messages.new-year'), 'New Year wishes'],
                    [route('messages.page', 'eid-mubarak-messages'), 'Eid Mubarak messages'],
                    [route('messages.page', 'good-morning-messages'), 'Good morning messages'],
                    [route('messages.page', 'good-night-messages'), 'Good night messages'],
                    [route('messages.page', 'anniversary-wishes'), 'Anniversary wishes'],
                    $live['messages'] ? [route('messages.index'), 'All messages'] : null,
                    $live['quotes'] ? [route('quotes.index'), 'Quotes'] : null,
                ])),
            ],
            'islamic' => [
                'label' => 'Islamic', 'url' => route('name-entries.index'),
                'intro' => 'The Quran with audio, Muslim names with sourced meanings and pronunciation, masnoon duas, naats and the 99 Names of Allah.',
                'links' => array_values(array_filter([
                    $live['quran'] ? [route('quran.index'), 'Quran with audio'] : null,
                    [route('name-entries.index'), 'Islamic names'],
                    [route('name-entries.list', 'boys'), 'Boys names'],
                    [route('name-entries.list', 'girls'), 'Girls names'],
                    [route('duas.index'), 'Masnoon duas'],
                    $live['naats'] ? [route('naats.index'), 'Naats'] : null,
                    [route('islamic.allah-names'), '99 Names of Allah'],
                    [route('islamic.calendar'), 'Islamic calendar'],
                ])),
            ],
            'urdu' => [
                'label' => 'Urdu', 'url' => route('poetry.index'),
                'intro' => 'Classic Urdu poetry and original Urdu quotes, jokes and messages.',
                'links' => [
                    [route('poetry.index'), 'Urdu poetry'],
                    [route('poetry.segment', 'ghalib'), 'Ghalib poetry'],
                    [route('poetry.segment', 'iqbal'), 'Iqbal poetry'],
                    [route('urdu-content.page', 'urdu-quotes'), 'Urdu quotes'],
                    [route('urdu-content.page', 'urdu-jokes'), 'Urdu jokes'],
                    [route('urdu-content.index'), 'Urdu corner (اردو)'],
                ],
            ],
            'wallpapers' => $live['wallpapers'] ? [
                'label' => 'Wallpapers', 'url' => route('wallpapers.index'),
                'intro' => 'Free HD wallpapers for phone and desktop, sorted by category.',
                'links' => array_merge(
                    [[route('wallpapers.index'), 'All wallpapers']],
                    array_map(fn ($c) => [route('wallpapers.category', $c), \Illuminate\Support\Str::headline($c).' wallpapers'], $live['wallpapers'])
                ),
            ] : null,
            'ai' => [
                'label' => 'AI Tools', 'url' => route('ai-tools.index'),
                'intro' => 'Small free AI tools for jokes, captions, greetings and birthday messages.',
                'links' => array_merge(
                    [[route('ai-tools.index'), 'All AI tools']],
                    array_map(fn ($slug, $title) => [route('ai-tools.show', $slug), $title], array_keys($live['ai']), $live['ai'])
                ),
            ],
        ];

        return array_filter($groups);
    }

    /**
     * Most-viewed published games in the last 30 days (real ContentView rows, human views only),
     * topped up with the newest games when there is not enough data. Never shows a count.
     */
    public static function popularGames(int $n): \Illuminate\Support\Collection
    {
        $ids = \App\Models\ContentView::where('content_type', (new \App\Models\Game)->getMorphClass())
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('content_id, count(*) c')->groupBy('content_id')->orderByDesc('c')->limit($n * 2)->pluck('content_id');
        $byViews = \App\Models\Game::where('status', 'published')->whereIn('id', $ids)->get()
            ->sortBy(fn ($g) => $ids->search($g->id))->values()->take($n);

        return $byViews->concat(\App\Models\Game::where('status', 'published')->whereNotIn('id', $byViews->pluck('id'))
            ->latest('id')->take($n - $byViews->count())->get());
    }

    /** One group, for the "Explore" block on that section's hub page. */
    public static function group(string $key): ?array
    {
        return self::groups()[$key] ?? null;
    }
}
