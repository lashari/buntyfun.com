<?php

namespace App\Http\Controllers;

use App\Content\SiteNav;
use App\Models\AiTool;
use App\Models\AllahName;
use App\Models\BirthdayMessage;
use App\Models\ContentView;
use App\Models\Dua;
use App\Models\Game;
use App\Models\NameEntry;
use App\Models\Poet;
use App\Models\Quiz;
use App\Models\Wallpaper;
use App\Services\Library\DailyPicker;

class HomeController extends Controller
{
    public function index(DailyPicker $picker)
    {
        // A daily pick that fails (nothing scheduled, nothing published) just hides that card.
        $daily = fn (string $type) => rescue(fn () => $picker->item($type), null, false);

        $games = SiteNav::popularGames(6);
        // "Popular" only when real views (last 30 days) ranked at least 3 of the games shown.
        $gameViews = ContentView::where('content_type', (new Game)->getMorphClass())
            ->where('created_at', '>=', now()->subDays(30))->distinct()->count('content_id');

        return view('home', [
            'quizzes' => Quiz::where('status', 'published')->latest()->take(3)->get(),
            'games' => $games,
            'gamesArePopular' => $gameViews >= 3,
            'dailyQuiz' => $daily('quiz'),
            'wishes' => BirthdayMessage::where('status', 'published')->latest()->take(3)->get(),
            'relationships' => BirthdayMessage::hubRelationships(),
            'names' => NameEntry::where('status', 'published')->latest()->take(4)->get(),
            'allahNames' => AllahName::where('status', 'published')->orderBy('number')->take(4)->get(),
            'dua' => $daily('dua') ?? Dua::where('status', 'published')->orderBy('sort')->first(),
            'poem' => $daily('poem'),
            'poets' => Poet::where('status', 'published')->withCount(['poems' => fn ($q) => $q->where('status', 'published')])
                ->orderByDesc('poems_count')->take(6)->get()->filter(fn ($p) => $p->poems_count > 0),
            'wallpapers' => Wallpaper::where('status', 'published')->latest()->take(3)->get(),
            'aiTools' => AiTool::where('status', 'published')->latest()->take(3)->get(),
        ]);
    }
}
