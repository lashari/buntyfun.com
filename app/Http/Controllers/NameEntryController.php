<?php

namespace App\Http\Controllers;

use App\Models\NameEntry;
use Illuminate\Http\Request;

class NameEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = NameEntry::where('status', 'published');

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $q->where('name_en', 'like', $like)->orWhere('meaning', 'like', $like)->orWhere('name_ur', 'like', $like)->orWhere('name_ar', 'like', $like);
            });
        }

        if ($gender = $request->query('gender')) {
            $query->where('gender', $gender);
        }

        if ($letter = $request->query('letter')) {
            $query->where('name_en', 'like', "{$letter}%");
        }

        $names = $query->with('audio')->orderBy('name_en')->paginate(24)->withQueryString();

        return view('name-entries.index', ['names' => $names]);
    }

    /** /islamic-names/boys, /girls, /quranic, /prophets, /sahaba */
    public function list(Request $request, string $list)
    {
        $def = NameEntry::LISTS[$list] ?? abort(404);
        $query = NameEntry::where('status', 'published');
        if (isset($def['gender'])) {
            $query->where('gender', $def['gender']);
        } elseif ($def['category'] === 'sahaba') {
            $query->whereIn('category', ['sahaba', 'sahabiyah']);
        } else {
            $query->where('category', $def['category']);
        }
        $names = $query->with('audio')->orderBy('name_en')->paginate(48)->withQueryString();

        return view('name-entries.list', ['names' => $names, 'list' => $list, 'def' => $def]);
    }

    public function show(NameEntry $nameEntry)
    {
        abort_unless($nameEntry->status === 'published', 404);
        \App\Models\ContentView::track($nameEntry);

        $related = NameEntry::where('status', 'published')
            ->where('gender', $nameEntry->gender)
            ->where('id', '!=', $nameEntry->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('name-entries.show', ['name' => $nameEntry->load('audio'), 'related' => $related]);
    }
}
