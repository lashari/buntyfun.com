<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesPublishing;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\NameEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NameEntryAdminController extends Controller
{
    use HandlesPublishing;
    use \App\Http\Controllers\Admin\Concerns\SavesContentMeta;

    public function index(Request $request)
    {
        $names = NameEntry::query()
            ->when($request->query('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->query('category'), fn ($q, $c) => $q->where('category', $c))
            ->when($request->query('q'), fn ($q, $t) => $q->where(fn ($w) => $w->where('name_en', 'like', '%'.addcslashes($t, '%_\\').'%')->orWhere('meaning', 'like', '%'.addcslashes($t, '%_\\').'%')))
            ->latest()->paginate(50)->withQueryString();
        $counts = NameEntry::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');

        return view('admin.name-entries.index', ['names' => $names, 'counts' => $counts]);
    }

    /** Publish / send back to review / unpublish many names at once. Publishing still needs a real source_ref. */
    public function bulk(Request $request)
    {
        $data = $request->validate(['ids' => ['required', 'array', 'max:500'], 'ids.*' => ['integer'], 'action' => ['required', 'in:published,in_review,draft']]);
        abort_unless($request->user()->canModerate(), 403);
        abort_if($data['action'] === 'published' && ! $request->user()->canPublish(), 403);
        $done = 0;
        foreach (NameEntry::whereIn('id', $data['ids'])->get() as $name) {
            if ($data['action'] === 'published' && (empty($name->source_ref) || stripos($name->source_ref, 'placeholder') !== false)) {
                continue;
            }
            $name->update(['status' => $data['action']] + ($data['action'] === 'published' && in_array($name->verification, ['needs_review', 'source_checked'], true) ? ['verification' => 'editor_checked'] : []));
            AuditLog::record($request->user(), 'status:'.$data['action'], 'name_entry', $name->id);
            $done++;
        }

        return back()->with('status', "{$done} name(s) updated.");
    }

    public function create()
    {
        return view('admin.name-entries.form', ['name' => new NameEntry]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name_en']);
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $name = NameEntry::create($data);
        AuditLog::record($request->user(), 'created', 'name_entry', $name->id);
        $this->saveMeta($request, $name);

        return redirect()->route('admin.name-entries.index')->with('status', 'Saved.');
    }

    public function edit(NameEntry $nameEntry)
    {
        return view('admin.name-entries.form', ['name' => $nameEntry]);
    }

    public function update(Request $request, NameEntry $nameEntry)
    {
        $data = $this->validated($request);
        $data['slug'] = ($data['slug'] ?? null) ?: $nameEntry->slug;
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $nameEntry->update($data);
        AuditLog::record($request->user(), 'updated', 'name_entry', $nameEntry->id);
        $this->saveMeta($request, $nameEntry);

        return redirect()->route('admin.name-entries.index')->with('status', 'Saved.');
    }

    public function destroy(Request $request, NameEntry $nameEntry)
    {
        $this->authorizeDelete($request);

        AuditLog::record($request->user(), 'deleted', 'name_entry', $nameEntry->id);
        $nameEntry->delete();

        return redirect()->route('admin.name-entries.index')->with('status', 'Deleted.');
    }

    public function publish(Request $request, NameEntry $nameEntry)
    {
        abort_if(
            empty($nameEntry->source_ref) || stripos($nameEntry->source_ref, 'placeholder') !== false,
            422,
            'Cannot publish a name entry with no verified source_ref.'
        );

        $this->publishModel($request, $nameEntry, 'name_entry');

        return back()->with('status', 'Published.');
    }

    public function unpublish(Request $request, NameEntry $nameEntry)
    {
        $this->unpublishModel($request, $nameEntry, 'name_entry');

        return back()->with('status', 'Unpublished.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name_en' => ['required', 'string', 'max:120'],
            'name_ar' => ['nullable', 'string', 'max:120'],
            'name_ur' => ['nullable', 'string', 'max:120'],
            'gender' => ['required', 'in:male,female,unisex'],
            'meaning' => ['required', 'string'],
            'meaning_ur' => ['nullable', 'string', 'max:2000'],
            'origin' => ['nullable', 'string', 'max:120'],
            'category' => ['required', 'in:'.implode(',', array_keys(NameEntry::CATEGORIES))],
            'quranic_ref' => ['nullable', 'string', 'max:190'],
            'verification' => ['required', 'in:'.implode(',', array_keys(\App\Models\NameEntry::VERIFICATION))],
            'pronunciation' => ['nullable', 'string', 'max:80'],
            'source_ref' => ['required', 'string', 'max:500'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,in_review,published'],
        ]);
    }
}
