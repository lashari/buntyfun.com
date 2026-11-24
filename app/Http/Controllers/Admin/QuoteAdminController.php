<?php

namespace App\Http\Controllers\Admin;

use App\Content\QuoteTopics;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Quote;
use App\Services\Library\QuoteImporter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuoteAdminController extends Controller
{
    use Concerns\SavesContentMeta;

    public function index(Request $request)
    {
        $status = $request->query('status', 'in_review');
        $topic = $request->query('topic');
        $quotes = Quote::query()
            ->when(isset(Quote::STATUSES[$status]), fn ($w) => $w->where('status', $status))
            ->when(isset(QuoteTopics::ALL[$topic]), fn ($w) => $w->where('topic', $topic))
            ->when($request->boolean('flagged'), fn ($w) => $w->whereNotNull('flags'))
            ->when($request->boolean('clean'), fn ($w) => $w->whereNull('flags'))
            ->when($s = trim((string) $request->query('q')), fn ($w) => $w->where(fn ($x) => $x->where('body', 'like', "%{$s}%")->orWhere('author', 'like', "%{$s}%")))
            ->orderBy('id')->paginate(50)->withQueryString();

        $counts = Quote::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');

        return view('admin.quotes.index', compact('quotes', 'status', 'topic', 'counts'));
    }

    public function import(Request $request, QuoteImporter $importer)
    {
        $amount = (int) $request->validate(['amount' => ['required', 'integer', 'min:1', 'max:1000']])['amount'];
        try {
            $s = $importer->importNext($amount);
        } catch (\Throwable $e) {
            return back()->withErrors(['import' => 'Import failed: '.mb_substr($e->getMessage(), 0, 200)]);
        }
        AuditLog::record($request->user(), 'imported', 'quote', 0);

        return back()->with('status', "Imported {$s['saved']} quote(s) as In review; {$s['duplicates']} duplicates, {$s['rejected']} rejected; {$s['remaining']} left in QuoteSlate.")
            ->with('reasons', array_slice($s['reasons'], 0, 15));
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'max:200'], 'ids.*' => ['integer'],
            'action' => ['required', Rule::in(['publish', 'reject', 'review'])],
        ]);
        abort_if($data['action'] === 'publish' && ! $request->user()->canPublish(), 403);
        $n = Quote::whereIn('id', $data['ids'])->update(['status' => ['publish' => 'published', 'reject' => 'rejected', 'review' => 'in_review'][$data['action']], 'updated_at' => now()]);
        AuditLog::record($request->user(), 'bulk_'.$data['action'], 'quote', $n);

        return back()->with('status', "{$n} quote(s) updated.");
    }

    public function edit(Quote $quote)
    {
        return view('admin.quotes.edit', ['quote' => $quote]);
    }

    public function update(Request $request, Quote $quote)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'min:10', 'max:350'],
            'author' => ['required', 'string', 'max:120'],
            'topic' => ['required', Rule::in(array_keys(QuoteTopics::ALL))],
            'status' => ['required', Rule::in(array_keys(Quote::STATUSES))],
        ]);
        if ($data['status'] === 'published' && ! $request->user()->canPublish()) {
            $data['status'] = 'in_review';
        }
        $hash = Quote::hashFor($data['body']);
        if (Quote::where('hash', $hash)->whereKeyNot($quote->id)->exists()) {
            return back()->withInput()->withErrors(['body' => 'This quote is already in the list.']);
        }
        $quote->update($data + ['hash' => $hash, 'author_slug' => Quote::slugForAuthor($data['author']), 'flags' => null]);
        $this->saveMeta($request, $quote);
        AuditLog::record($request->user(), 'updated', 'quote', $quote->id);

        return redirect()->route('admin.quotes.index')->with('status', 'Quote saved.');
    }
}
