<?php

namespace App\Http\Controllers;

use App\Models\BirthdayMessage;
use Illuminate\Http\Request;

class BirthdayMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = BirthdayMessage::where('status', 'published');

        if ($relationship = $request->query('relationship')) {
            $query->where('relationship', $relationship);
        }

        if ($tone = $request->query('tone')) {
            $query->where('tone', $tone);
        }

        if ($language = $request->query('language')) {
            $query->where('language', $language);
        }

        $messages = $query->latest()->paginate(12)->withQueryString();

        return view('birthday-messages.index', [
            'messages' => $messages,
            'relationships' => BirthdayMessage::where('status', 'published')->distinct()->pluck('relationship'),
            'tones' => BirthdayMessage::where('status', 'published')->distinct()->pluck('tone'),
        ]);
    }

    public function show(BirthdayMessage $birthdayMessage)
    {
        abort_unless($birthdayMessage->status === 'published', 404);

        return view('birthday-messages.show', ['message' => $birthdayMessage]);
    }
}
