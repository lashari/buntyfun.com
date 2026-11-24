<?php

namespace App\Http\Controllers;

use App\Content\UrduPages;
use App\Models\UrduContent;
use Illuminate\Http\Request;

class UrduContentController extends Controller
{
    public function index(Request $request)
    {
        $query = UrduContent::where('status', 'published');

        if ($category = $request->query('category')) {
            // Topics with their own page: one URL per topic for search engines.
            if ($page = UrduPages::forCategory($category)) {
                return redirect()->route('urdu-content.page', $page, 301);
            }
            $query->where('category', $category);
        }

        $content = $query->latest()->paginate(12)->withQueryString();

        return view('urdu.index', [
            'content' => $content,
            'categories' => UrduContent::where('status', 'published')->distinct()->pluck('category'),
            'pages' => collect(UrduPages::PAGES)->map(fn ($p, $url) => $p + [
                'count' => UrduContent::indexable()->where('category', $p['category'])->count(),
            ]),
        ]);
    }

    /** /urdu-quotes etc.: hand-written lines grouped by section, plus other published rows in the topic. */
    public function page(string $urduPage)
    {
        $page = UrduPages::PAGES[$urduPage];
        $all = UrduContent::indexable()->where('category', $page['category'])->orderBy('id')->get();
        $sections = collect($page['sections'])->keys()
            ->mapWithKeys(fn ($key) => [$key => $all->filter(fn ($m) => UrduPages::sectionOf($page, $m->slug) === $key)->sortBy('slug')->values()])
            ->filter(fn ($items) => $items->isNotEmpty());
        $more = $all->filter(fn ($m) => UrduPages::sectionOf($page, $m->slug) === null)->values();

        return view('urdu.page', [
            'page' => $page,
            'sections' => $sections,
            'more' => $more,
            'count' => $all->count(),
            'indexable' => $all->count() >= UrduPages::MIN_FOR_INDEX,
            'others' => collect(UrduPages::PAGES)->except($urduPage),
        ]);
    }

    public function show(UrduContent $urduContent)
    {
        abort_unless($urduContent->status === 'published', 404);
        \App\Models\ContentView::track($urduContent);

        return view('urdu.show', ['item' => $urduContent]);
    }
}
