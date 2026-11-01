<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesPublishing;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GameAdminController extends Controller
{
    use HandlesPublishing;

    public function index()
    {
        return view('admin.games.index', ['games' => Game::with('category')->latest()->paginate(20)]);
    }

    public function create()
    {
        return view('admin.games.form', ['game' => new Game, 'categories' => Category::where('department', 'games')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $game = Game::create($data);
        AuditLog::record($request->user(), 'created', 'game', $game->id);

        return redirect()->route('admin.games.index')->with('status', 'Game saved.');
    }

    public function edit(Game $game)
    {
        return view('admin.games.form', ['game' => $game, 'categories' => Category::where('department', 'games')->get()]);
    }

    public function update(Request $request, Game $game)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $game->update($data);
        AuditLog::record($request->user(), 'updated', 'game', $game->id);

        return redirect()->route('admin.games.index')->with('status', 'Game saved.');
    }

    public function destroy(Request $request, Game $game)
    {
        AuditLog::record($request->user(), 'deleted', 'game', $game->id);
        $game->delete();

        return redirect()->route('admin.games.index')->with('status', 'Game deleted.');
    }

    public function publish(Request $request, Game $game)
    {
        $this->publishModel($request, $game, 'game');

        return back()->with('status', 'Game published.');
    }

    public function unpublish(Request $request, Game $game)
    {
        $this->unpublishModel($request, $game, 'game');

        return back()->with('status', 'Game unpublished.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,in_review,published'],
        ]);
    }
}
