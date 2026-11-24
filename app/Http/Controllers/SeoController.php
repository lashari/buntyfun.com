<?php

namespace App\Http\Controllers;

use App\Models\AiTool;
use App\Models\BirthdayMessage;
use App\Models\Game;
use App\Models\Message;
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
            ['loc' => route('ai-tools.index'), 'changefreq' => 'weekly'],
            ['loc' => route('pages.about'), 'changefreq' => 'yearly'],
            ['loc' => route('pages.contact'), 'changefreq' => 'yearly'],
            ['loc' => route('pages.privacy'), 'changefreq' => 'yearly'],
            ['loc' => route('pages.terms'), 'changefreq' => 'yearly'],
            ['loc' => route('pages.editorial'), 'changefreq' => 'yearly'],
        ]);

        // Empty sections are noindex until something is published — keep them out of the sitemap too.
        if (Message::indexable()->exists()) {
            $urls->push(['loc' => route('messages.index'), 'changefreq' => 'daily']);
        }
        if (Wallpaper::indexable()->exists()) {
            $urls->push(['loc' => route('wallpapers.index'), 'changefreq' => 'weekly']);
            foreach (Wallpaper::indexable()->distinct()->orderBy('category')->pluck('category') as $category) {
                $urls->push(['loc' => route('wallpapers.category', $category), 'changefreq' => 'weekly']);
            }
        }

        if (Message::indexable()->where('category', 'new_year')->count() >= \App\Content\NewYearWishes::MIN_FOR_INDEX) {
            $urls->push(['loc' => route('messages.new-year'), 'changefreq' => 'weekly']);
        }
        foreach (\App\Content\MessagePages::PAGES as $url => $page) {
            if (Message::indexable()->where('category', $page['category'])->count() >= \App\Content\MessagePages::MIN_FOR_INDEX) {
                $urls->push(['loc' => route('messages.page', $url), 'changefreq' => 'weekly']);
            }
        }
        foreach (\App\Content\UrduPages::PAGES as $url => $page) {
            if (UrduContent::indexable()->where('category', $page['category'])->count() >= \App\Content\UrduPages::MIN_FOR_INDEX) {
                $urls->push(['loc' => route('urdu-content.page', $url), 'changefreq' => 'weekly']);
            }
        }
        if (BirthdayMessage::urduPageIndexable()) {
            $urls->push(['loc' => route('birthday-messages.urdu'), 'changefreq' => 'weekly']);
        }
        foreach (BirthdayMessage::hubRelationships() as $relationship) {
            $urls->push(['loc' => route('birthday-messages.relationship', BirthdayMessage::relationshipSlug($relationship)), 'changefreq' => 'weekly']);
        }

        // Single wishes / Urdu lines / messages are one short text each: noindex by default
        // (config site.index_single_snippets), so they are not listed here either.
        $snippets = config('site.index_single_snippets');

        if ($snippets) {
            foreach (Message::indexable()->get() as $message) {
                $urls->push(['loc' => route('messages.show', $message), 'changefreq' => 'monthly', 'lastmod' => $message->updated_at]);
            }
        }

        // Quizzes under 5 questions are noindex on the page itself (too thin to rank or carry ads).
        foreach (Quiz::indexable()->has('questions', '>=', 5)->get() as $quiz) {
            $urls->push(['loc' => route('quizzes.show', $quiz), 'changefreq' => 'monthly', 'lastmod' => $quiz->updated_at]);
        }

        foreach (Game::indexable()->get() as $game) {
            $urls->push(['loc' => route('games.show', $game), 'changefreq' => 'monthly']);
        }

        if ($snippets) {
            foreach (BirthdayMessage::indexable()->get() as $message) {
                $urls->push(['loc' => route('birthday-messages.show', $message), 'changefreq' => 'monthly']);
            }
        }

        foreach (NameEntry::indexable()->get() as $name) {
            $urls->push(['loc' => route('name-entries.show', $name), 'changefreq' => 'yearly']);
        }

        if ($snippets) {
            foreach (UrduContent::indexable()->get() as $item) {
                $urls->push(['loc' => route('urdu-content.show', $item), 'changefreq' => 'monthly']);
            }
        }

        foreach (Wallpaper::indexable()->get() as $wallpaper) {
            $urls->push(['loc' => route('wallpapers.show', $wallpaper), 'changefreq' => 'yearly']);
        }

        foreach (AiTool::indexable()->get() as $tool) {
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
            'Disallow: /admin',
            'Disallow: /search',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }

    /** RSS 2.0: newest published wishes, messages, Urdu items, names and quizzes. */
    public function feed(): Response
    {
        $items = collect()
            ->merge(BirthdayMessage::indexable()->latest('updated_at')->limit(15)->get()->map(fn ($m) => [
                'title' => ucwords(str_replace('_', ' ', $m->tone)).' birthday wish for '.str_replace('_', ' ', $m->relationship),
                'link' => route('birthday-messages.show', $m), 'text' => $m->body, 'date' => $m->updated_at]))
            ->merge(Message::indexable()->latest('updated_at')->limit(15)->get()->map(fn ($m) => [
                'title' => $m->title ?: $m->categoryLabel(), 'link' => route('messages.show', $m), 'text' => $m->body, 'date' => $m->updated_at]))
            ->merge(UrduContent::indexable()->latest('updated_at')->limit(10)->get()->map(fn ($m) => [
                'title' => 'اردو: '.$m->category, 'link' => route('urdu-content.show', $m), 'text' => $m->body, 'date' => $m->updated_at]))
            ->merge(NameEntry::indexable()->latest('updated_at')->limit(10)->get()->map(fn ($m) => [
                'title' => $m->name_en.' — name meaning', 'link' => route('name-entries.show', $m), 'text' => $m->meaning, 'date' => $m->updated_at]))
            ->merge(Quiz::indexable()->latest('updated_at')->limit(10)->get()->map(fn ($m) => [
                'title' => $m->title, 'link' => route('quizzes.show', $m), 'text' => 'Take the quiz: '.$m->title, 'date' => $m->updated_at]))
            ->sortByDesc('date')->take(30)->values();

        return response(view('seo.feed', ['items' => $items])->render(), 200)
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
