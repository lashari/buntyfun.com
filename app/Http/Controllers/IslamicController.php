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
        $duas = Dua::where('status', 'published')->orderBy('category')->orderBy('sort')->get()->groupBy('category');

        return view('islamic.duas', ['groups' => $duas]);
    }

    public function dua(Dua $dua)
    {
        abort_unless($dua->status === 'published', 404);
        \App\Models\ContentView::track($dua);
        $more = Dua::where('status', 'published')->whereKeyNot($dua->id)->where('category', $dua->category)->orderBy('sort')->limit(6)->get();

        return view('islamic.dua', ['dua' => $dua, 'more' => $more]);
    }

    public function calendar()
    {
        return view('islamic.calendar');
    }
}
