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
            'poem' => \Illuminate\Support\Facades\Schema::hasTable('poems') ? $picker->item('poem')?->load('poet') : null,
            'dua' => \Illuminate\Support\Facades\Schema::hasTable('duas') ? $picker->item('dua') : null,
        ]);
    }
}
