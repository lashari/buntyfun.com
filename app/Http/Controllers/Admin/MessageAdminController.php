<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesPublishing;
use App\Http\Controllers\Admin\Concerns\SavesContentMeta;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MessageAdminController extends Controller
{
    use HandlesPublishing;
    use SavesContentMeta;

    public function index(Request $request)
    {
        $query = Message::latest();
        if ($c = $request->query('category')) {
            $query->where('category', $c);
        }

        return view('admin.messages.index', ['items' => $query->paginate(25)->withQueryString()]);
    }

    public function create()
    {
        return view('admin.messages.form', ['item' => new Message]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug(($data['title'] ?: $data['category']).'-'.Str::random(5));
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $item = Message::create($data);
        AuditLog::record($request->user(), 'created', 'message', $item->id);
        $this->saveMeta($request, $item);

        return redirect()->route('admin.messages.index')->with('status', 'Saved.');
    }

    public function edit(Message $message)
    {
        return view('admin.messages.form', ['item' => $message]);
    }

    public function update(Request $request, Message $message)
    {
        $data = $this->validated($request, $message);
        $data['slug'] = ($data['slug'] ?? null) ?: $message->slug;
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $message->update($data);
        AuditLog::record($request->user(), 'updated', 'message', $message->id);
        $this->saveMeta($request, $message);

        return redirect()->route('admin.messages.index')->with('status', 'Saved.');
    }

    public function destroy(Request $request, Message $message)
    {
        $this->authorizeDelete($request);
        AuditLog::record($request->user(), 'deleted', 'message', $message->id);
        $message->meta()?->delete();
        $message->delete();

        return redirect()->route('admin.messages.index')->with('status', 'Deleted.');
    }

    public function publish(Request $request, Message $message)
    {
        $this->publishModel($request, $message, 'message');

        return back()->with('status', 'Published.');
    }

    public function unpublish(Request $request, Message $message)
    {
        $this->unpublishModel($request, $message, 'message');

        return back()->with('status', 'Unpublished.');
    }

    private function validated(Request $request, ?Message $message = null): array
    {
        return $request->validate([
            'category' => ['required', Rule::in(array_keys(Message::CATEGORIES))],
            'language' => ['required', 'in:en,ur'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('messages', 'slug')->ignore($message?->id)],
            'status' => ['required', 'in:draft,in_review,published'],
        ]);
    }
}
