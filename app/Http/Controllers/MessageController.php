<?php

namespace App\Http\Controllers;

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
