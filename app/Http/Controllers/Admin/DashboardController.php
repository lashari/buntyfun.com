<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BirthdayMessage;
use App\Models\ContentView;
use App\Models\Game;
use App\Models\NameEntry;
use App\Models\Quiz;
use App\Models\ShareEvent;

class DashboardController extends Controller
{
    public function index()
    {
        $counts = [
            'quizzes' => ['total' => Quiz::count(), 'published' => Quiz::where('status', 'published')->count()],
            'games' => ['total' => Game::count(), 'published' => Game::where('status', 'published')->count()],
            'birthday_messages' => ['total' => BirthdayMessage::count(), 'published' => BirthdayMessage::where('status', 'published')->count()],
            'name_entries' => ['total' => NameEntry::count(), 'published' => NameEntry::where('status', 'published')->count()],
        ];

        $views = ContentView::count();
        $shares = ShareEvent::count();

        return view('admin.dashboard', ['counts' => $counts, 'views' => $views, 'shares' => $shares]);
    }
}
