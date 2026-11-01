<?php

namespace App\Http\Controllers;

use App\Models\AiTool;
use App\Models\BirthdayMessage;
use App\Models\Game;
use App\Models\NameEntry;
use App\Models\Quiz;
use App\Models\UrduContent;
use App\Models\Wallpaper;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'changefreq' => 'daily'],
            ['loc' => route('quizzes.index'), 'changefreq' => 'daily'],
            ['loc' => route('games.index'), 'changefreq' => 'weekly'],
            ['loc' => route('birthday-messages.index'), 'changefreq' => 'weekly'],
            ['loc' => route('name-entries.index'), 'changefreq' => 'weekly'],
            ['loc' => route('urdu-content.index'), 'changefreq' => 'weekly'],
            ['loc' => route('wallpapers.index'), 'changefreq' => 'weekly'],
            ['loc' => route('ai-tools.index'), 'changefreq' => 'weekly'],
        ]);

        foreach (Quiz::where('status', 'published')->get() as $quiz) {
            $urls->push(['loc' => route('quizzes.show', $quiz), 'changefreq' => 'monthly']);
        }

        foreach (Game::where('status', 'published')->get() as $game) {
            $urls->push(['loc' => route('games.show', $game), 'changefreq' => 'monthly']);
        }

        foreach (BirthdayMessage::where('status', 'published')->get() as $message) {
            $urls->push(['loc' => route('birthday-messages.show', $message), 'changefreq' => 'monthly']);
        }

        foreach (NameEntry::where('status', 'published')->get() as $name) {
            $urls->push(['loc' => route('name-entries.show', $name), 'changefreq' => 'yearly']);
        }

        foreach (UrduContent::where('status', 'published')->get() as $item) {
            $urls->push(['loc' => route('urdu-content.show', $item), 'changefreq' => 'monthly']);
        }

        foreach (Wallpaper::where('status', 'published')->get() as $wallpaper) {
            $urls->push(['loc' => route('wallpapers.show', $wallpaper), 'changefreq' => 'yearly']);
        }

        foreach (AiTool::where('status', 'published')->get() as $tool) {
            $urls->push(['loc' => route('ai-tools.show', $tool), 'changefreq' => 'monthly']);
        }

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }
}
