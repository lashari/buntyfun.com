<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesPublishing;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\UrduContent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UrduContentAdminController extends Controller
{
    use HandlesPublishing;

    public function index()
    {
        return view('admin.urdu-content.index', ['items' => UrduContent::latest()->paginate(20)]);
    }

    public function create()
    {
        return view('admin.urdu-content.form', ['item' => new UrduContent]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['category'].'-'.Str::random(6));
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $item = UrduContent::create($data);
        AuditLog::record($request->user(), 'created', 'urdu_content', $item->id);

        return redirect()->route('admin.urdu-content.index')->with('status', 'Saved.');
    }

    public function edit(UrduContent $urduContent)
    {
        return view('admin.urdu-content.form', ['item' => $urduContent]);
    }

    public function update(Request $request, UrduContent $urduContent)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: $urduContent->slug;
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $urduContent->update($data);
        AuditLog::record($request->user(), 'updated', 'urdu_content', $urduContent->id);

        return redirect()->route('admin.urdu-content.index')->with('status', 'Saved.');
    }

    public function destroy(Request $request, UrduContent $urduContent)
    {
        $this->authorizeDelete($request);

        AuditLog::record($request->user(), 'deleted', 'urdu_content', $urduContent->id);
        $urduContent->delete();

        return redirect()->route('admin.urdu-content.index')->with('status', 'Deleted.');
    }

    public function publish(Request $request, UrduContent $urduContent)
    {
        $this->publishModel($request, $urduContent, 'urdu_content');

        return back()->with('status', 'Published.');
    }

    public function unpublish(Request $request, UrduContent $urduContent)
    {
        $this->unpublishModel($request, $urduContent, 'urdu_content');

        return back()->with('status', 'Unpublished.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category' => ['required', 'string', 'max:60'],
            'body' => ['required', 'string'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,in_review,published'],
        ]);
    }
}
