<?php

namespace App\Http\Controllers;

use App\Content\QuoteTopics;
use App\Models\Quote;
use App\Services\Library\DailyPicker;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public const MIN_AUTHOR_INDEX = 5;

    public function index(Request $request, DailyPicker $picker)
    {
        $q = trim((string) $request->query('q', ''));
        $results = null;
        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], mb_substr($q, 0, 80)).'%';
            $results = Quote::where('status', 'published')
                ->where(fn ($w) => $w->where('body', 'like', $like)->orWhere('author', 'like', $like))
                ->orderBy('id')->paginate(30)->withQueryString();
        }

        return view('quotes.index', [
            'q' => $q,
            'results' => $results,
            'today' => $picker->item('quote'),
            'topics' => $this->topicCounts(),
            'authors' => Quote::where('status', 'published')->where('author', '!=', 'Anonymous')
                ->selectRaw('author, author_slug, count(*) c')->groupBy('author', 'author_slug')
                ->having('c', '>=', self::MIN_AUTHOR_INDEX)->orderByDesc('c')->orderBy('author')->get(), // all indexable authors: each one in the sitemap needs a link
            'total' => Quote::where('status', 'published')->count(),
        ]);
    }

    public function topic(string $topic)
    {
        abort_unless(isset(QuoteTopics::ALL[$topic]), 404);
        $quotes = Quote::where('status', 'published')->where('topic', $topic)->orderBy('id')->paginate(30);
        abort_if($quotes->total() === 0, 404);

        return view('quotes.list', [
            'kind' => 'topic', 'heading' => QuoteTopics::label($topic), 'intro' => QuoteTopics::ALL[$topic][1],
            'quotes' => $quotes, 'noindex' => $quotes->total() < QuoteTopics::MIN_FOR_INDEX || $quotes->currentPage() > 1,
            'topics' => $this->topicCounts(), 'current' => $topic,
        ]);
    }

    public function author(string $author)
    {
        $quotes = Quote::where('status', 'published')->where('author_slug', $author)->orderBy('id')->paginate(30);
        abort_if($quotes->total() === 0, 404);
        $name = $quotes->first()->author;
        $topics = Quote::where('status', 'published')->where('author_slug', $author)->distinct()->pluck('topic');

        return view('quotes.list', [
            'kind' => 'author', 'heading' => $name.' Quotes',
            'intro' => $quotes->total().' '.\Illuminate\Support\Str::plural('quote', $quotes->total()).' by '.$name.', on '
                .$topics->map(fn ($t) => strtolower(str_replace(' Quotes', '', QuoteTopics::label($t))))->take(5)->implode(', ')
                .'. Tap a quote to copy it or share it on WhatsApp.',
            'quotes' => $quotes, 'noindex' => $quotes->total() < self::MIN_AUTHOR_INDEX || $quotes->currentPage() > 1 || $name === 'Anonymous',
            'topics' => $this->topicCounts(), 'current' => null,
        ]);
    }

    /** Single quotes are one line each: shareable, but noindex (the topic pages carry them). */
    public function show(Quote $quote)
    {
        abort_unless($quote->status === 'published', 404);
        $more = Quote::where('status', 'published')->where('topic', $quote->topic)->whereKeyNot($quote->id)->inRandomOrder()->limit(6)->get();

        return view('quotes.show', ['quote' => $quote, 'more' => $more]);
    }

    public static function topicCounts(): array
    {
        $counts = Quote::where('status', 'published')->selectRaw('topic, count(*) c')->groupBy('topic')->pluck('c', 'topic');

        return collect(QuoteTopics::ALL)->keys()->filter(fn ($t) => ($counts[$t] ?? 0) > 0)
            ->mapWithKeys(fn ($t) => [$t => (int) $counts[$t]])->sortDesc()->all();
    }
}
