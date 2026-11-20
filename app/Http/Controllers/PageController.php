<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/** Static trust pages (About, Contact, Privacy, Terms, Editorial policy) and ads.txt. */
class PageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function editorial()
    {
        return view('pages.editorial');
    }

    /** Built from the real AdSense client id; 404 until one is configured — never a placeholder. */
    public function adsTxt(): Response
    {
        $client = (string) config('services.adsense.client_id');
        abort_unless(preg_match('/^ca-(pub-\d{10,20})$/', $client, $m), 404);

        return response("google.com, {$m[1]}, DIRECT, f08c47fec0942fa0\n", 200)
            ->header('Content-Type', 'text/plain');
    }
}
