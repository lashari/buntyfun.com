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

    public function index()
    {
        return view('admin.name-entries.index', ['names' => NameEntry::latest()->paginate(20)]);
    }

    public function create()
    {
        return view('admin.name-entries.form', ['name' => new NameEntry]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name_en']);
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $name = NameEntry::create($data);
        AuditLog::record($request->user(), 'created', 'name_entry', $name->id);

        return redirect()->route('admin.name-entries.index')->with('status', 'Saved.');
    }

    public function edit(NameEntry $nameEntry)
    {
        return view('admin.name-entries.form', ['name' => $nameEntry]);
    }

    public function update(Request $request, NameEntry $nameEntry)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: $nameEntry->slug;
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $nameEntry->update($data);
        AuditLog::record($request->user(), 'updated', 'name_entry', $nameEntry->id);

        return redirect()->route('admin.name-entries.index')->with('status', 'Saved.');
    }

    public function destroy(Request $request, NameEntry $nameEntry)
    {
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
            'origin' => ['nullable', 'string', 'max:120'],
            'source_ref' => ['required', 'string', 'max:500'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,in_review,published'],
        ]);
    }
}
