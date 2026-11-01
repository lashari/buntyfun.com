<?php

namespace App\Http\Controllers;

use App\Models\Wallpaper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WallpaperController extends Controller
{
    public function index(Request $request)
    {
        $query = Wallpaper::where('status', 'published');

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        $wallpapers = $query->latest()->paginate(18)->withQueryString();

        return view('wallpapers.index', [
            'wallpapers' => $wallpapers,
            'categories' => Wallpaper::where('status', 'published')->distinct()->pluck('category'),
        ]);
    }

    public function show(Wallpaper $wallpaper)
    {
        abort_unless($wallpaper->status === 'published', 404);

        return view('wallpapers.show', ['wallpaper' => $wallpaper]);
    }

    public function download(Wallpaper $wallpaper)
    {
        abort_unless($wallpaper->status === 'published', 404);
        abort_unless(Storage::disk('public')->exists($wallpaper->file_path), 404);

        return Storage::disk('public')->download(
            $wallpaper->file_path,
            $wallpaper->slug.'.'.pathinfo($wallpaper->file_path, PATHINFO_EXTENSION)
        );
    }
}
