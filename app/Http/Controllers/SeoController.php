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

        $urls->push(['loc' => route('daily'), 'changefreq' => 'daily']);
        foreach (\App\Http\Controllers\QuizController::topicCounts() as $topic => $count) {
            if ($count >= 2) {
                $urls->push(['loc' => route('quizzes.topic', $topic), 'changefreq' => 'weekly']);
            }
        }
        if (\App\Models\Quote::where('status', 'published')->count() >= 10) {
            $urls->push(['loc' => route('quotes.index'), 'changefreq' => 'daily']);
            foreach (\App\Http\Controllers\QuoteController::topicCounts() as $topic => $count) {
                if ($count >= \App\Content\QuoteTopics::MIN_FOR_INDEX) {
                    $urls->push(['loc' => route('quotes.topic', $topic), 'changefreq' => 'weekly']);
                }
            }
            $authors = \App\Models\Quote::where('status', 'published')->where('author', '!=', 'Anonymous')
                ->selectRaw('author_slug, count(*) c')->groupBy('author_slug')->having('c', '>=', \App\Http\Controllers\QuoteController::MIN_AUTHOR_INDEX)->pluck('author_slug');
            foreach ($authors as $slug) {
                $urls->push(['loc' => route('quotes.author', $slug), 'changefreq' => 'monthly']);
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

        // Single wallpaper pages only with a real caption; the rest stay noindex (category pages carry them).
        foreach (Wallpaper::indexable()->get()->filter->hasUsefulDescription() as $wallpaper) {
            $urls->push(['loc' => route('wallpapers.show', $wallpaper), 'changefreq' => 'yearly']);
        }

        foreach (AiTool::indexable()->get() as $tool) {
            $urls->push(['loc' => route('ai-tools.show', $tool), 'changefreq' => 'monthly']);
        }

        $this->libraryUrls($urls);

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    /** Islamic knowledge + Urdu poetry pages (11-26-2026). Lists only pages that have published content. */
    private function libraryUrls(\Illuminate\Support\Collection $urls): void
    {
        $urls->push(['loc' => route('islamic.calendar'), 'changefreq' => 'monthly']);
        // Before the library SQL is run (tables/columns missing) list nothing else rather than fail the whole sitemap.
        if (! \Illuminate\Support\Facades\Schema::hasTable('poems') || ! \Illuminate\Support\Facades\Schema::hasColumn('name_entries', 'category')) {
            return;
        }
        foreach (NameEntry::LISTS as $key => $def) {
            $q = NameEntry::where('status', 'published');
            isset($def['gender']) ? $q->where('gender', $def['gender']) : ($def['category'] === 'sahaba' ? $q->whereIn('category', ['sahaba', 'sahabiyah']) : $q->where('category', $def['category']));
            if ($q->count() >= 5) {
                $urls->push(['loc' => route('name-entries.list', $key), 'changefreq' => 'weekly']);
            }
        }
        if (\App\Models\AllahName::where('status', 'published')->count() >= 99) {
            $urls->push(['loc' => route('islamic.allah-names'), 'changefreq' => 'yearly']);
        }
        $duas = \App\Models\Dua::indexable()->get();
        if ($duas->isNotEmpty()) {
            $urls->push(['loc' => route('duas.index'), 'changefreq' => 'monthly']);
            foreach ($duas as $dua) {
                $urls->push(['loc' => route('duas.show', $dua), 'changefreq' => 'yearly', 'lastmod' => $dua->updated_at]);
            }
        }
        $poems = \App\Models\Poem::indexable()->with('poet:id,slug')->get(['id', 'poet_id', 'slug', 'form', 'mood', 'updated_at']);
        if ($poems->isEmpty()) {
            return;
        }
        $urls->push(['loc' => route('poetry.index'), 'changefreq' => 'daily']);
        foreach ($poems->groupBy(fn ($p) => $p->poet->slug) as $slug => $list) {
            $urls->push(['loc' => route('poetry.segment', $slug), 'changefreq' => 'weekly']);
        }
        foreach ([$poems->countBy('form'), $poems->whereNotNull('mood')->countBy('mood')] as $counts) {
            foreach ($counts as $key => $c) {
                if ($c >= 5) {
                    $urls->push(['loc' => route('poetry.segment', $key), 'changefreq' => 'weekly']);
                }
            }
        }
        foreach ($poems as $poem) {
            $urls->push(['loc' => route('poetry.show', [$poem->poet->slug, $poem->slug]), 'changefreq' => 'yearly', 'lastmod' => $poem->updated_at]);
        }
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
