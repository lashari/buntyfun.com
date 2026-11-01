<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::where('status', 'published')->latest()->get();

        return view('games.index', ['games' => $games]);
    }

    public function show(Game $game)
    {
        abort_unless($game->status === 'published', 404);

        $view = 'games.play.'.str_replace('-', '_', $game->slug);
        abort_unless(view()->exists($view), 404);

        return view($view, ['game' => $game]);
    }

    public function storeScore(Request $request, Game $game)
    {
        $data = $request->validate([
            'nickname' => ['nullable', 'string', 'max:40'],
            'score' => ['required', 'integer', 'min:0'],
        ]);

        $game->scores()->create($data);

        return response()->json(['ok' => true]);
    }
}
