<?php

namespace App\Http\Controllers;

use App\Services\Library\DailyPicker;

class DailyController extends Controller
{
    public function index(DailyPicker $picker)
    {
        return view('daily.index', [
            'trivia' => $picker->trivia(),
            'quiz' => $picker->item('quiz'),
            'quote' => $picker->item('quote'),
            'game' => $picker->item('game'),
        ]);
    }
}
