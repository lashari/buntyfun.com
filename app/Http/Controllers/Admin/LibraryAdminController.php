<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllahName;
use App\Models\AuditLog;
use App\Models\Dua;
use App\Models\Poem;
use App\Models\Poet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Admin → Library: poems, poets, duas and the 99 Names in one place.
 * Draft → In review → Published → Archived, bulk actions, search/filters, JSON/CSV import
 * (always saved In review, duplicates skipped) and an audit-log row for every change.
 */
class LibraryAdminController extends Controller
{
    public const STATUSES = ['draft' => 'Draft', 'in_review' => 'In review', 'published' => 'Published', 'archived' => 'Archived'];

    /** type => model, list columns, editable fields [name => [label, input, rules]], unique key for duplicate checks. */
    private function types(): array
    {
        $text = fn ($max = 190) => ['nullable', 'string', 'max:'.$max];

        return [
            'poems' => ['label' => 'Poems', 'model' => Poem::class, 'search' => ['title', 'body'], 'unique' => 'hash', 'order' => 'id',
                'fields' => [
                    'poet_id' => ['Poet', 'poet', ['required', 'integer', 'exists:poets,id']],
                    'title' => ['Title (Urdu)', 'text-rtl', ['required', 'string', 'max:255']],
                    'form' => ['Form', 'select:'.implode(',', array_keys(Poem::FORMS)), ['required', Rule::in(array_keys(Poem::FORMS))]],
                    'mood' => ['Mood', 'select:,'.implode(',', array_keys(Poem::MOODS)), ['nullable', Rule::in(array_keys(Poem::MOODS))]],
                    'book' => ['Book', 'text-rtl', $text()],
                    'body' => ['Text (one line per line, blank line between couplets)', 'textarea-rtl', ['required', 'string', 'max:20000']],
                    'source_url' => ['Source URL', 'text', ['nullable', 'url', 'max:500']],
                    'license' => ['Licence', 'text', $text(40)],
                ]],
            'poets' => ['label' => 'Poets', 'model' => Poet::class, 'search' => ['name_en', 'name_ur'], 'unique' => 'slug', 'order' => 'born',
                'fields' => [
                    'name_en' => ['Name (English)', 'text', ['required', 'string', 'max:120']],
                    'name_ur' => ['Name (Urdu)', 'text-rtl', ['required', 'string', 'max:120']],
                    'slug' => ['URL slug', 'text', ['required', 'alpha_dash', 'max:80']],
                    'born' => ['Born (year)', 'number', ['nullable', 'integer', 'between:1000,2100']],
                    'died' => ['Died (year)', 'number', ['nullable', 'integer', 'between:1000,2100']],
                    'bio_en' => ['Biography (English)', 'textarea', ['nullable', 'string', 'max:5000']],
                    'bio_ur' => ['Biography (Urdu)', 'textarea-rtl', ['nullable', 'string', 'max:5000']],
                    'wikipedia' => ['Wikipedia URL', 'text', ['nullable', 'url', 'max:255']],
                ]],
            'duas' => ['label' => 'Duas', 'model' => Dua::class, 'search' => ['title', 'translation_en', 'reference'], 'unique' => 'slug', 'order' => 'category',
                'fields' => [
                    'title' => ['Title', 'text', ['required', 'string', 'max:190']],
                    'slug' => ['URL slug', 'text', ['required', 'alpha_dash', 'max:160']],
                    'category' => ['Category', 'select:'.implode(',', array_keys(Dua::CATEGORIES)), ['required', Rule::in(array_keys(Dua::CATEGORIES))]],
                    'arabic' => ['Arabic', 'textarea-rtl', ['required', 'string', 'max:3000']],
                    'transliteration' => ['Transliteration', 'textarea', ['nullable', 'string', 'max:3000']],
                    'translation_en' => ['Meaning (English)', 'textarea', ['required', 'string', 'max:3000']],
                    'translation_ur' => ['Meaning (Urdu)', 'textarea-rtl', ['nullable', 'string', 'max:3000']],
                    'reference' => ['Reference (Quran or hadith number)', 'text', ['required', 'string', 'max:190']],
                    'source_url' => ['Source URL (quran.com / sunnah.com)', 'text', ['nullable', 'url', 'max:255']],
                    'sort' => ['Order in category', 'number', ['nullable', 'integer', 'min:0', 'max:999']],
                ]],
            'allah-names' => ['label' => '99 Names of Allah', 'model' => AllahName::class, 'search' => ['transliteration', 'meaning_en', 'meaning_ur'], 'unique' => 'number', 'order' => 'number',
                'fields' => [
                    'number' => ['Number (1–99)', 'number', ['required', 'integer', 'between:1,99']],
                    'arabic' => ['Arabic', 'text-rtl', ['required', 'string', 'max:80']],
                    'transliteration' => ['Transliteration', 'text', ['required', 'string', 'max:80']],
                    'meaning_en' => ['Meaning (English)', 'text', ['required', 'string', 'max:190']],
                    'meaning_ur' => ['Meaning (Urdu)', 'text-rtl', $text()],
                    'quran_ref' => ['Quran reference (optional)', 'text', $text(120)],
                ]],
        ];
    }

    private function type(string $type): array
    {
        return $this->types()[$type] ?? abort(404);
    }

    public function index(Request $request, string $type = 'poems')
    {
        $t = $this->type($type);
        $model = $t['model'];
        $status = $request->query('status', 'in_review');
        $query = $model::query();
        if (isset(self::STATUSES[$status])) {
            $query->where('status', $status);
        }
        if ($q = trim((string) $request->query('q'))) {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $query->where(fn ($w) => collect($t['search'])->each(fn ($c) => $w->orWhere($c, 'like', $like)));
        }
        if ($type === 'poems') {
            $query->with('poet')
                ->when($request->integer('poet'), fn ($w, $p) => $w->where('poet_id', $p))
                ->when($request->query('form'), fn ($w, $f) => $w->where('form', $f))
                ->when($request->query('mood'), fn ($w, $m) => $m === 'none' ? $w->whereNull('mood') : $w->where('mood', $m));
        }
        $items = $query->orderBy($t['order'])->orderBy('id')->paginate(50)->withQueryString();
        $counts = $model::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $tabs = collect($this->types())->map(fn ($x) => $x['label']);

        return view('admin.library.index', [
            'type' => $type, 't' => $t, 'items' => $items, 'counts' => $counts, 'status' => $status, 'tabs' => $tabs,
            'poets' => $type === 'poems' ? Poet::orderBy('born')->pluck('name_en', 'id') : collect(),
        ]);
    }

    public function edit(string $type, int $id)
    {
        $t = $this->type($type);

        return view('admin.library.form', ['type' => $type, 't' => $t, 'item' => $t['model']::findOrFail($id), 'poets' => Poet::orderBy('born')->pluck('name_en', 'id')]);
    }

    public function create(string $type)
    {
        $t = $this->type($type);

        return view('admin.library.form', ['type' => $type, 't' => $t, 'item' => new $t['model'], 'poets' => Poet::orderBy('born')->pluck('name_en', 'id')]);
    }

    public function save(Request $request, string $type, ?int $id = null)
    {
        $t = $this->type($type);
        /** @var Model $item */
        $item = $id ? $t['model']::findOrFail($id) : new $t['model'];
        $rules = collect($t['fields'])->map(fn ($f) => $f[2])->all();
        $rules['status'] = ['required', Rule::in(array_keys(self::STATUSES))];
        $key = $t['unique'];
        if (isset($rules[$key])) {
            $rules[$key][] = Rule::unique($item->getTable(), $key)->ignore($item->id);
        }
        $data = $request->validate($rules);
        if ($data['status'] === 'published' && ! $request->user()->canPublish()) {
            $data['status'] = 'in_review';
        }
        if ($type === 'poems') {
            $data['body'] = trim(str_replace("\r", '', $data['body']));
            $data['hash'] = Poem::hashBody($data['body']);
            $data['line_count'] = count(array_filter(explode("\n", $data['body']), fn ($l) => trim($l) !== ''));
            if ($t['model']::where('hash', $data['hash'])->whereKeyNot($item->id)->exists()) {
                return back()->withInput()->withErrors(['body' => 'The same poem text is already in the library.']);
            }
            if (! $item->exists) {
                $poet = Poet::find($data['poet_id']);
                $data['slug'] = $poet->slug.'-'.$data['form'].'-'.Str::lower(Str::random(6));
                $data['source'] = 'admin';
            }
        }
        $item->fill($data)->save();
        AuditLog::record($request->user(), $id ? 'updated' : 'created', Str::singular(str_replace('-', '_', $type)), $item->id);

        return redirect()->route('admin.library.index', ['type' => $type, 'status' => $item->status])->with('status', 'Saved.');
    }

    public function bulk(Request $request, string $type)
    {
        $t = $this->type($type);
        $data = $request->validate(['ids' => ['required', 'array', 'max:500'], 'ids.*' => ['integer'], 'action' => ['required', Rule::in(array_keys(self::STATUSES))]]);
        abort_if($data['action'] === 'published' && ! $request->user()->canPublish(), 403);
        abort_unless($request->user()->canModerate(), 403);
        $items = $t['model']::whereIn('id', $data['ids'])->get();
        foreach ($items as $item) {
            $item->update(['status' => $data['action']]);
            AuditLog::record($request->user(), 'status:'.$data['action'], Str::singular(str_replace('-', '_', $type)), $item->id);
        }

        return back()->with('status', $items->count().' item(s) set to '.self::STATUSES[$data['action']].'.');
    }

    /** JSON (array of objects) or CSV (header row) upload. Rows are validated, duplicates skipped, saved In review. */
    public function import(Request $request, string $type)
    {
        $t = $this->type($type);
        abort_unless($request->user()->canModerate(), 403);
        $request->validate(['file' => ['required', 'file', 'max:5120', 'mimes:json,csv,txt']]);
        $raw = (string) file_get_contents($request->file('file')->getRealPath());
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        $rows = json_decode($raw, true);
        if (! is_array($rows)) {
            $lines = array_map('str_getcsv', preg_split('/\r\n|\n|\r/', trim($raw)));
            $head = array_map('trim', array_shift($lines) ?: []);
            $rows = array_values(array_filter(array_map(fn ($l) => count($l) === count($head) ? array_combine($head, $l) : null, $lines)));
        }
        $made = 0;
        $skipped = [];
        $poetIds = Poet::pluck('id', 'slug');
        foreach (array_slice($rows, 0, 2000) as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            if ($type === 'poems' && isset($row['poet']) && ! isset($row['poet_id'])) {
                $row['poet_id'] = $poetIds[$row['poet']] ?? null;
            }
            $row = array_intersect_key($row, $t['fields']);
            $v = validator($row, collect($t['fields'])->map(fn ($f) => $f[2])->all());
            if ($v->fails()) {
                $skipped[] = 'Row '.($i + 1).': '.$v->errors()->first();

                continue;
            }
            $data = $v->validated();
            if ($type === 'poems') {
                $data['body'] = trim(str_replace("\r", '', $data['body']));
                $data['hash'] = Poem::hashBody($data['body']);
                $data['line_count'] = count(array_filter(explode("\n", $data['body']), fn ($l) => trim($l) !== ''));
                $data['slug'] = Poet::find($data['poet_id'])->slug.'-'.$data['form'].'-'.Str::lower(Str::random(6));
                $data['source'] = 'import';
            }
            $key = $t['unique'];
            if ($t['model']::where($key, $data[$key])->exists()) {
                $skipped[] = 'Row '.($i + 1).': duplicate ('.$key.')';

                continue;
            }
            $data['status'] = $type === 'poets' ? 'draft' : 'in_review';
            $item = $t['model']::create($data);
            AuditLog::record($request->user(), 'imported', Str::singular(str_replace('-', '_', $type)), $item->id);
            $made++;
        }

        return back()->with('status', "Imported {$made} item(s) as ".($type === 'poets' ? 'Draft' : 'In review').'; skipped '.count($skipped).'.')
            ->with('reasons', array_slice($skipped, 0, 30));
    }
}
