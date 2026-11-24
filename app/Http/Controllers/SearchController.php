<?php

namespace App\Http\Controllers;

use App\Models\AiTool;
use App\Models\BirthdayMessage;
use App\Models\Game;
use App\Models\Message;
use App\Models\NameEntry;
use App\Models\Quiz;
use App\Models\Quote;
use App\Models\UrduContent;
use App\Models\Wallpaper;
use Illuminate\Http\Request;

/** Site search: published items only, each section capped so the page stays short. */
class SearchController extends Controller
{
    public const PER_SECTION = 8;

    public function index(Request $request)
    {
        $q = mb_substr(trim((string) $request->query('q', '')), 0, 80);
        $results = array_fill_keys(['quizzes', 'games', 'quotes', 'wishes', 'messages', 'names', 'urdu', 'wallpapers', 'aiTools'], collect());

        if (mb_strlen($q) >= 2) {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q).'%';
            $any = fn ($query, array $cols) => $query->where('status', 'published')
                ->where(fn ($w) => collect($cols)->each(fn ($c) => $w->orWhere($c, 'like', $like)))->take(self::PER_SECTION)->get();

            $results['quizzes'] = $any(Quiz::query(), ['title', 'intro']);
            $results['games'] = $any(Game::query(), ['title', 'slug']);
            $results['quotes'] = $any(Quote::query(), ['body', 'author']);
            $results['wishes'] = $any(BirthdayMessage::query(), ['body']);
            $results['messages'] = $any(Message::query(), ['title', 'body']);
            $results['names'] = $any(NameEntry::query(), ['name_en', 'meaning']);
            $results['urdu'] = $any(UrduContent::query(), ['body']);
            $results['wallpapers'] = $any(Wallpaper::query(), ['title', 'category']);
            $results['aiTools'] = $any(AiTool::query(), ['title']);
        }

        return view('search.index', ['q' => $q, 'results' => $results]);
    }
}
