<?php

namespace App\Http\Controllers;

use App\Models\AiTool;
use App\Models\BirthdayMessage;
use App\Models\Game;
use App\Models\NameEntry;
use App\Models\Quiz;
use App\Models\Wallpaper;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'quizzes' => Quiz::where('status', 'published')->latest()->take(3)->get(),
            'games' => Game::where('status', 'published')->latest()->take(3)->get(),
            'wishes' => BirthdayMessage::where('status', 'published')->latest()->take(3)->get(),
            'names' => NameEntry::where('status', 'published')->latest()->take(3)->get(),
            'wallpapers' => Wallpaper::where('status', 'published')->latest()->take(3)->get(),
            'aiTools' => AiTool::where('status', 'published')->latest()->take(3)->get(),
        ]);
    }
}
