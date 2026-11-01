<?php

namespace App\Http\Controllers;

use App\Models\BirthdayMessage;
use App\Models\Game;
use App\Models\NameEntry;
use App\Models\Quiz;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'quizzes' => Quiz::where('status', 'published')->latest()->take(3)->get(),
            'games' => Game::where('status', 'published')->latest()->take(3)->get(),
            'wishes' => BirthdayMessage::where('status', 'published')->latest()->take(3)->get(),
            'names' => NameEntry::where('status', 'published')->latest()->take(3)->get(),
        ]);
    }
}
