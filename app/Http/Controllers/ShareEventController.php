<?php

namespace App\Http\Controllers;

use App\Models\ShareEvent;
use Illuminate\Http\Request;

class ShareEventController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'content_type' => ['required', 'string', 'max:40'],
            'content_id' => ['required', 'integer'],
            'channel' => ['required', 'string', 'max:40'],
        ]);

        ShareEvent::create($data);

        return response()->json(['ok' => true]);
    }
}
