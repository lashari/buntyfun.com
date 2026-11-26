<?php

namespace App\Http\Controllers;

use App\Models\Naat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/** Naat library: only Published naats with a licence note are shown. */
class NaatController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Schema::hasTable('naats'), 404);
        $q = trim(mb_substr((string) $request->query('q'), 0, 60));
        $query = Naat::where('status', 'published')->with('audio');
        if ($q !== '') {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $query->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('title_ur', 'like', $like)->orWhere('poet', 'like', $like)->orWhere('reciter', 'like', $like));
        }
        $naats = $query->orderBy('title')->get();

        return view('naats.index', ['naats' => $naats, 'q' => $q, 'category' => null,
            'counts' => Naat::where('status', 'published')->selectRaw('category, count(*) c')->groupBy('category')->pluck('c', 'category')]);
    }

    public function category(string $category)
    {
        abort_unless(Schema::hasTable('naats') && isset(Naat::CATEGORIES[$category]), 404);
        $naats = Naat::where('status', 'published')->where('category', $category)->with('audio')->orderBy('title')->get();

        return view('naats.index', ['naats' => $naats, 'q' => '', 'category' => $category,
            'counts' => Naat::where('status', 'published')->selectRaw('category, count(*) c')->groupBy('category')->pluck('c', 'category')]);
    }

    public function show(Naat $naat)
    {
        abort_unless($naat->status === 'published', 404);
        \App\Models\ContentView::track($naat);
        $more = Naat::where('status', 'published')->whereKeyNot($naat->id)->where('category', $naat->category)->orderBy('title')->limit(6)->get();

        return view('naats.show', ['naat' => $naat->load('audio'), 'more' => $more]);
    }
}
