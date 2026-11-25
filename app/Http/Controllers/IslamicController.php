<?php

namespace App\Http\Controllers;

use App\Models\AllahName;
use App\Models\Dua;

class IslamicController extends Controller
{
    public function allahNames()
    {
        return view('islamic.allah-names', ['names' => AllahName::where('status', 'published')->orderBy('number')->get()]);
    }

    public function duas()
    {
        $duas = Dua::withListen(Dua::where('status', 'published')->orderBy('category')->orderBy('sort')->get())->groupBy('category');

        return view('islamic.duas', ['groups' => $duas, 'counts' => self::categoryCounts(), 'reciters' => self::reciters()]);
    }

    /** /duas/c/{category}: one category per page (morning & evening, sleep, travel, prayer …). */
    public function duaCategory(string $category)
    {
        abort_unless(isset(Dua::CATEGORIES[$category]), 404);
        $duas = Dua::withListen(Dua::where('status', 'published')->where('category', $category)->orderBy('sort')->orderBy('id')->get());
        abort_if($duas->isEmpty(), 404);

        return view('islamic.dua-category', ['category' => $category, 'duas' => $duas, 'counts' => self::categoryCounts(), 'reciters' => self::reciters()]);
    }

    /** Reciters for the Listen button on Quranic duas: [{slug, name, pattern}]. */
    public static function reciters(): array
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('quran_reciters')) {
            return [];
        }

        return \App\Models\QuranReciter::live()->map(fn ($r) => ['slug' => $r->slug, 'name' => $r->name, 'pattern' => $r->url_pattern])->values()->all();
    }

    public static function categoryCounts()
    {
        return Dua::where('status', 'published')->selectRaw('category, count(*) c')->groupBy('category')->pluck('c', 'category');
    }

    public function dua(Dua $dua)
    {
        abort_unless($dua->status === 'published', 404);
        \App\Models\ContentView::track($dua);
        $more = Dua::where('status', 'published')->whereKeyNot($dua->id)->where('category', $dua->category)->orderBy('sort')->limit(6)->get();

        Dua::withListen(collect([$dua]));

        return view('islamic.dua', ['dua' => $dua, 'more' => $more, 'reciters' => self::reciters()]);
    }

    public function calendar()
    {
        return view('islamic.calendar');
    }
}
