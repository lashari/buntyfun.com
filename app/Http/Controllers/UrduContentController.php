<?php

namespace App\Http\Controllers;

use App\Models\UrduContent;
use Illuminate\Http\Request;

class UrduContentController extends Controller
{
    public function index(Request $request)
    {
        $query = UrduContent::where('status', 'published');

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        $content = $query->latest()->paginate(12)->withQueryString();

        return view('urdu.index', [
            'content' => $content,
            'categories' => UrduContent::where('status', 'published')->distinct()->pluck('category'),
        ]);
    }

    public function show(UrduContent $urduContent)
    {
        abort_unless($urduContent->status === 'published', 404);

        return view('urdu.show', ['item' => $urduContent]);
    }
}
