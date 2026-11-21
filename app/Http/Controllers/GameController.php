<?php

namespace App\Http\Controllers;

use App\Content\OssGames;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $games = Game::where('status', 'published')->latest()->get();

        $for = $request->query('for');
        if (is_string($for) && array_key_exists($for, OssGames::AUDIENCES)) {
            $games = $games->filter(fn (Game $g) => in_array($for, OssGames::audienceOf($g), true))->values();
        } else {
            $for = null;
        }

        return view('games.index', ['games' => $games, 'for' => $for]);
    }

    public function show(Request $request, Game $game)
    {
        // Admins (moderator and up) can open an unpublished game to test it before approving.
        $preview = $game->status !== 'published';
        abort_if($preview && ! $request->user()?->canModerate(), 404);
        if (! $preview) {
            \App\Models\ContentView::track($game);
        }

        $view = 'games.play.'.str_replace('-', '_', $game->slug);
        if (view()->exists($view)) {
            return view($view, ['game' => $game]);
        }

        // Open-source games bundled under public/play/{slug}/.
        $oss = OssGames::for($game->slug);
        abort_unless($oss, 404);

        return view('games.embed', ['game' => $game, 'oss' => $oss, 'src' => OssGames::src($game->slug), 'preview' => $preview]);
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
