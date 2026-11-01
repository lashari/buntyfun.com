<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesPublishing;
use App\Http\Controllers\Controller;
use App\Models\AiTool;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiToolAdminController extends Controller
{
    use HandlesPublishing;

    public function index()
    {
        return view('admin.ai-tools.index', ['tools' => AiTool::orderBy('title')->get()]);
    }

    public function create()
    {
        return view('admin.ai-tools.form', ['tool' => new AiTool]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $tool = AiTool::create($data);
        AuditLog::record($request->user(), 'created', 'ai_tool', $tool->id);

        return redirect()->route('admin.ai-tools.index')->with('status', 'Saved.');
    }

    public function edit(AiTool $aiTool)
    {
        return view('admin.ai-tools.form', ['tool' => $aiTool]);
    }

    public function update(Request $request, AiTool $aiTool)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: $aiTool->slug;
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $aiTool->update($data);
        AuditLog::record($request->user(), 'updated', 'ai_tool', $aiTool->id);

        return redirect()->route('admin.ai-tools.index')->with('status', 'Saved.');
    }

    public function destroy(Request $request, AiTool $aiTool)
    {
        AuditLog::record($request->user(), 'deleted', 'ai_tool', $aiTool->id);
        $aiTool->delete();

        return redirect()->route('admin.ai-tools.index')->with('status', 'Deleted.');
    }

    public function publish(Request $request, AiTool $aiTool)
    {
        $this->publishModel($request, $aiTool, 'ai_tool');

        return back()->with('status', 'Published.');
    }

    public function unpublish(Request $request, AiTool $aiTool)
    {
        $this->unpublishModel($request, $aiTool, 'ai_tool');

        return back()->with('status', 'Unpublished.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'prompt_template' => ['required', 'string', 'max:2000'],
            'status' => ['required', 'in:draft,in_review,published'],
        ]);
    }
}
