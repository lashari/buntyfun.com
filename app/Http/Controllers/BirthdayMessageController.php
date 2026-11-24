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
            'hubs' => BirthdayMessage::hubRelationships(),
        ]);
    }

    /** Indexable hub: every published wish for one relationship, plus written guidance. */
    public function relationship(string $relationship)
    {
        $key = str_replace('-', '_', $relationship);
        $guide = \App\Content\BirthdayGuides::for($key);
        abort_unless($guide && in_array($key, BirthdayMessage::hubRelationships(), true), 404);

        $wishes = BirthdayMessage::where('status', 'published')->where('relationship', $key)
            ->orderBy('tone')->orderBy('language')->orderBy('id')->get()->groupBy('tone');

        $others = collect(BirthdayMessage::hubRelationships())->reject(fn ($r) => $r === $key)->values();

        return view('birthday-messages.relationship', compact('key', 'guide', 'wishes', 'others'));
    }

    /** Indexable page of every published Urdu wish, grouped by who it is for. */
    public function urdu()
    {
        $wishes = BirthdayMessage::where('status', 'published')->where('language', 'ur')
            ->orderBy('relationship')->orderBy('tone')->orderBy('id')->get()->groupBy('relationship');
        $order = array_keys(\App\Content\BirthdayGuides::ALL);
        $wishes = $wishes->sortBy(fn ($items, $r) => array_search($r, $order, true) === false ? 99 : array_search($r, $order, true));

        return view('birthday-messages.urdu', ['wishes' => $wishes, 'count' => $wishes->flatten()->count(), 'indexable' => BirthdayMessage::urduPageIndexable()]);
    }

    public function show(BirthdayMessage $birthdayMessage)
    {
        abort_unless($birthdayMessage->status === 'published', 404);
        \App\Models\ContentView::track($birthdayMessage);

        return view('birthday-messages.show', ['message' => $birthdayMessage]);
    }
}
