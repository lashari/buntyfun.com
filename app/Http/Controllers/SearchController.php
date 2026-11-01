<?php

namespace App\Http\Controllers;

use App\Models\AiTool;
use App\Models\BirthdayMessage;
use App\Models\Game;
use App\Models\NameEntry;
use App\Models\Quiz;
use App\Models\UrduContent;
use App\Models\Wallpaper;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $results = [
            'quizzes' => collect(),
            'games' => collect(),
            'wishes' => collect(),
            'names' => collect(),
            'urdu' => collect(),
            'wallpapers' => collect(),
            'aiTools' => collect(),
        ];

        if ($q !== '') {
            $results['quizzes'] = Quiz::where('status', 'published')->where('title', 'like', "%{$q}%")->take(5)->get();
            $results['games'] = Game::where('status', 'published')->where('title', 'like', "%{$q}%")->take(5)->get();
            $results['wishes'] = BirthdayMessage::where('status', 'published')->where('body', 'like', "%{$q}%")->take(5)->get();
            $results['names'] = NameEntry::where('status', 'published')->where('name_en', 'like', "%{$q}%")->take(5)->get();
            $results['urdu'] = UrduContent::where('status', 'published')->where('body', 'like', "%{$q}%")->take(5)->get();
            $results['wallpapers'] = Wallpaper::where('status', 'published')->where('title', 'like', "%{$q}%")->take(5)->get();
            $results['aiTools'] = AiTool::where('status', 'published')->where('title', 'like', "%{$q}%")->take(5)->get();
        }

        return view('search.index', ['q' => $q, 'results' => $results]);
    }
}
