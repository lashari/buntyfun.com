<?php

namespace App\Http\Controllers;

use App\Models\Wallpaper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WallpaperController extends Controller
{
    /** Category name => published count, biggest first. */
    private function categoryCounts()
    {
        return Wallpaper::where('status', 'published')
            ->selectRaw('category, count(*) as total')->groupBy('category')
            ->orderByDesc('total')->orderBy('category')->pluck('total', 'category');
    }

    /** All wallpapers page: one row of 6 per category, each with "View all". */
    public function index(Request $request)
    {
        // old ?category= links → the category's own page
        if ($category = $request->query('category')) {
            return redirect()->route('wallpapers.category', $category, 301);
        }

        $counts = $this->categoryCounts();
        $sections = $counts->keys()->mapWithKeys(fn ($category) => [
            $category => Wallpaper::where('status', 'published')->where('category', $category)->latest('id')->take(6)->get(),
        ]);

        return view('wallpapers.index', ['counts' => $counts, 'sections' => $sections]);
    }

    public function category(string $category)
    {
        $counts = $this->categoryCounts();
        abort_unless($counts->has($category), 404);

        $wallpapers = Wallpaper::where('status', 'published')->where('category', $category)
            ->latest('id')->paginate(24);

        return view('wallpapers.category', [
            'category' => $category,
            'counts' => $counts,
            'wallpapers' => $wallpapers,
        ]);
    }

    public function show(Wallpaper $wallpaper)
    {
        abort_unless($wallpaper->status === 'published', 404);
        \App\Models\ContentView::track($wallpaper);

        // Back / Next inside the same category, same order as the category page (newest first).
        $inCategory = Wallpaper::where('status', 'published')->where('category', $wallpaper->category);
        $previous = (clone $inCategory)->where('id', '>', $wallpaper->id)->orderBy('id')->first();
        $next = (clone $inCategory)->where('id', '<', $wallpaper->id)->orderByDesc('id')->first();
        $position = (clone $inCategory)->where('id', '>=', $wallpaper->id)->count();
        $total = (clone $inCategory)->count();

        return view('wallpapers.show', compact('wallpaper', 'previous', 'next', 'position', 'total'));
    }
    public function download(Wallpaper $wallpaper)
    {
        abort_unless($wallpaper->status === 'published', 404);

        if ($wallpaper->remote_url) {
            // Unsplash API terms: tell Unsplash about each download, then send the user to their file.
            if ($wallpaper->download_location && config('services.unsplash.access_key')) {
                try {
                    \App\Services\Import\UnsplashSource::client()->timeout(5)->get($wallpaper->download_location);
                } catch (\Throwable) {
                    // never block the visitor's download on a tracking ping
                }
            }

            return redirect()->away($wallpaper->remote_url);
        }

        abort_unless(Storage::disk('public')->exists($wallpaper->file_path), 404);

        return Storage::disk('public')->download(
            $wallpaper->file_path,
            $wallpaper->slug.'.'.pathinfo($wallpaper->file_path, PATHINFO_EXTENSION)
        );
    }
}
