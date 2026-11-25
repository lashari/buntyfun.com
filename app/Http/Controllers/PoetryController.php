<?php

namespace App\Http\Controllers;

use App\Models\Poem;
use App\Models\Poet;
use App\Services\Library\DailyPicker;
use Illuminate\Http\Request;

class PoetryController extends Controller
{
    public function index(Request $request, DailyPicker $picker)
    {
        $poets = Poet::where('status', 'published')->withCount(['poems' => fn ($q) => $q->where('status', 'published')])->orderBy('born')->get();
        $q = trim((string) $request->query('q', ''));
        $results = null;
        if ($q !== '') {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $results = Poem::where('status', 'published')->where(fn ($w) => $w->where('body', 'like', $like)->orWhere('title', 'like', $like))
                ->with('poet')->limit(40)->get();
        }
        $counts = Poem::where('status', 'published')->selectRaw('form, count(*) c')->groupBy('form')->pluck('c', 'form');
        $moods = Poem::where('status', 'published')->whereNotNull('mood')->selectRaw('mood, count(*) c')->groupBy('mood')->pluck('c', 'mood');

        return view('poetry.index', [
            'poets' => $poets, 'q' => $q, 'results' => $results, 'counts' => $counts, 'moods' => $moods,
            'today' => $picker->item('poem'),
            'latest' => Poem::where('status', 'published')->with('poet')->latest('updated_at')->limit(8)->get(),
        ]);
    }

    /** /urdu-poetry/{segment}: a poet (ghalib), a form (ghazal) or a mood (sad). */
    public function segment(Request $request, string $segment)
    {
        if (isset(Poem::FORMS[$segment]) || isset(Poem::MOODS[$segment])) {
            $isForm = isset(Poem::FORMS[$segment]);
            $label = ($isForm ? Poem::FORMS : Poem::MOODS)[$segment];
            $poems = Poem::where('status', 'published')->where($isForm ? 'form' : 'mood', $segment)->with('poet')
                ->orderBy('poet_id')->orderBy('id')->paginate(30);
            abort_if($poems->isEmpty() && $request->query('page'), 404);

            return view('poetry.category', ['key' => $segment, 'label' => $label, 'isForm' => $isForm, 'poems' => $poems]);
        }

        $poet = Poet::where('slug', $segment)->where('status', 'published')->firstOrFail();
        $form = $request->query('form');
        $poems = $poet->poems()->where('status', 'published')
            ->when(is_string($form) && isset(Poem::FORMS[$form]), fn ($q) => $q->where('form', $form))
            ->orderBy('id')->paginate(40)->withQueryString();
        $forms = $poet->poems()->where('status', 'published')->selectRaw('form, count(*) c')->groupBy('form')->pluck('c', 'form');

        return view('poetry.poet', ['poet' => $poet, 'poems' => $poems, 'forms' => $forms, 'form' => $form]);
    }

    public function show(Request $request, string $poetSlug, string $poemSlug)
    {
        $poem = Poem::where('slug', $poemSlug)->with('poet')->firstOrFail();
        abort_unless($poem->poet && $poem->poet->slug === $poetSlug, 404);
        $preview = $poem->status !== 'published';
        abort_if($preview && ! $request->user()?->canModerate(), 404);
        if (! $preview) {
            \App\Models\ContentView::track($poem);
        }
        $siblings = Poem::where('status', 'published')->where('poet_id', $poem->poet_id);
        $prev = (clone $siblings)->where('id', '<', $poem->id)->orderByDesc('id')->first();
        $next = (clone $siblings)->where('id', '>', $poem->id)->orderBy('id')->first();
        $more = Poem::where('status', 'published')->where('poet_id', $poem->poet_id)->whereKeyNot($poem->id)
            ->where('form', $poem->form)->inRandomOrder()->limit(6)->get();

        return view('poetry.show', compact('poem', 'prev', 'next', 'more', 'preview'));
    }
}
