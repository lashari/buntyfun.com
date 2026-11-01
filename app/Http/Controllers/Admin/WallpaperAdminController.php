<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesPublishing;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Wallpaper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WallpaperAdminController extends Controller
{
    use HandlesPublishing;

    public function index()
    {
        return view('admin.wallpapers.index', ['wallpapers' => Wallpaper::latest()->paginate(20)]);
    }

    public function create()
    {
        return view('admin.wallpapers.form', ['wallpaper' => new Wallpaper]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, requireImage: true);
        $data = $this->storeUpload($request, $data);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']).'-'.Str::random(6);
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $wallpaper = Wallpaper::create($data);
        AuditLog::record($request->user(), 'created', 'wallpaper', $wallpaper->id);

        return redirect()->route('admin.wallpapers.index')->with('status', 'Saved.');
    }

    public function edit(Wallpaper $wallpaper)
    {
        return view('admin.wallpapers.form', ['wallpaper' => $wallpaper]);
    }

    public function update(Request $request, Wallpaper $wallpaper)
    {
        $data = $this->validated($request, requireImage: false);
        if ($request->hasFile('image')) {
            $data = $this->storeUpload($request, $data);
            Storage::disk('public')->delete($wallpaper->file_path);
        }
        $data['slug'] = $data['slug'] ?: $wallpaper->slug;
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $wallpaper->update($data);
        AuditLog::record($request->user(), 'updated', 'wallpaper', $wallpaper->id);

        return redirect()->route('admin.wallpapers.index')->with('status', 'Saved.');
    }

    public function destroy(Request $request, Wallpaper $wallpaper)
    {
        $this->authorizeDelete($request);

        Storage::disk('public')->delete($wallpaper->file_path);
        AuditLog::record($request->user(), 'deleted', 'wallpaper', $wallpaper->id);
        $wallpaper->delete();

        return redirect()->route('admin.wallpapers.index')->with('status', 'Deleted.');
    }

    public function publish(Request $request, Wallpaper $wallpaper)
    {
        $this->publishModel($request, $wallpaper, 'wallpaper');

        return back()->with('status', 'Published.');
    }

    public function unpublish(Request $request, Wallpaper $wallpaper)
    {
        $this->unpublishModel($request, $wallpaper, 'wallpaper');

        return back()->with('status', 'Unpublished.');
    }

    private function validated(Request $request, bool $requireImage): array
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:60'],
            'orientation' => ['required', 'in:desktop,mobile,both'],
            'license' => ['required', Rule::in(['public_domain', 'cc0', 'cc_by', 'original', 'licensed'])],
            'attribution' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'status' => ['required', 'in:draft,in_review,published'],
            // Explicit raster mimes, not the generic 'image' rule — that allows SVG,
            // which can embed <script> and executes if the stored file's raw URL is
            // ever opened directly rather than rendered inside an <img> tag.
            'image' => [$requireImage ? 'required' : 'nullable', 'mimes:jpeg,png,webp,gif', 'max:10240'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $license = $request->input('license');
            if (in_array($license, ['cc_by', 'licensed'], true) && ! $request->filled('attribution')) {
                $validator->errors()->add('attribution', 'Attribution is required for CC-BY or licensed images.');
            }
        });

        return $validator->validate();
    }

    private function storeUpload(Request $request, array $data): array
    {
        $file = $request->file('image');
        [$width, $height] = getimagesize($file->getRealPath());

        $path = $file->store('wallpapers', 'public');

        return [...$data, 'file_path' => $path, 'width' => $width, 'height' => $height];
    }
}
