<?php

namespace App\Http\Controllers;

use App\Content\MessagePages;
use App\Content\NewYearWishes;
use App\Models\ContentView;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');
        $query = Message::where('status', 'published')->with('meta');
        if ($category && isset(Message::CATEGORIES[$category])) {
            // Categories with their own page: one URL per topic for search engines.
            if ($page = MessagePages::forCategory($category)) {
                return redirect()->route('messages.page', $page, 301);
            }
            $query->where('category', $category);
        } else {
            $category = null;
        }

        return view('messages.index', [
            'messages' => $query->latest()->paginate(12)->withQueryString(),
            'category' => $category,
            'categories' => Message::where('status', 'published')->distinct()->pluck('category')
                ->mapWithKeys(fn ($c) => [$c => Message::CATEGORIES[$c][0] ?? $c]),
        ]);
    }

    /** /new-year-wishes: hand-written New Year wishes grouped by section, plus any other published new_year messages. */
    public function newYear()
    {
        $all = Message::indexable()->where('category', 'new_year')->orderBy('id')->get();
        $sections = collect(NewYearWishes::SECTIONS)->keys()
            ->mapWithKeys(fn ($key) => [$key => $all->filter(fn ($m) => NewYearWishes::sectionOf($m->slug) === $key)->sortBy('slug')->values()])
            ->filter(fn ($items) => $items->isNotEmpty());
        $more = $all->filter(fn ($m) => NewYearWishes::sectionOf($m->slug) === null)->values();

        return view('messages.new-year', [
            'sections' => $sections,
            'more' => $more,
            'count' => $all->count(),
            'indexable' => $all->count() >= NewYearWishes::MIN_FOR_INDEX,
        ]);
    }

    /** /good-morning-messages etc.: hand-written messages grouped by section, plus other published messages in the category. */
    public function page(string $messagePage)
    {
        $page = MessagePages::PAGES[$messagePage];
        $all = Message::indexable()->where('category', $page['category'])->orderBy('id')->get();
        $sections = collect($page['sections'])->keys()
            ->mapWithKeys(fn ($key) => [$key => $all->filter(fn ($m) => MessagePages::sectionOf($page, $m->slug) === $key)->sortBy('slug')->values()])
            ->filter(fn ($items) => $items->isNotEmpty());
        $more = $all->filter(fn ($m) => MessagePages::sectionOf($page, $m->slug) === null)->values();

        $others = collect(MessagePages::PAGES)->except($messagePage)->map(fn ($p) => $p['h1']);

        return view('messages.page', [
            'url' => $messagePage,
            'page' => $page,
            'sections' => $sections,
            'more' => $more,
            'count' => $all->count(),
            'indexable' => $all->count() >= MessagePages::MIN_FOR_INDEX,
            'ads' => Message::CATEGORIES[$page['category']][1] ?? true,
            'others' => $others,
        ]);
    }

    public function show(Message $message)
    {
        abort_unless($message->status === 'published', 404);
        ContentView::track($message);

        return view('messages.show', [
            'message' => $message->load('meta'),
            'related' => Message::where('status', 'published')->where('category', $message->category)
                ->whereKeyNot($message->id)->inRandomOrder()->limit(4)->get(),
        ]);
    }
}
