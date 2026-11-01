<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesPublishing;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BirthdayMessage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BirthdayMessageAdminController extends Controller
{
    use HandlesPublishing;

    public function index()
    {
        return view('admin.birthday-messages.index', ['messages' => BirthdayMessage::latest()->paginate(20)]);
    }

    public function create()
    {
        return view('admin.birthday-messages.form', [
            'message' => new BirthdayMessage,
            'categories' => Category::where('department', 'birthday_wishes')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['relationship'].'-'.$data['tone'].'-'.Str::random(6));
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $message = BirthdayMessage::create($data);
        AuditLog::record($request->user(), 'created', 'birthday_message', $message->id);

        return redirect()->route('admin.birthday-messages.index')->with('status', 'Saved.');
    }

    public function edit(BirthdayMessage $birthdayMessage)
    {
        return view('admin.birthday-messages.form', [
            'message' => $birthdayMessage,
            'categories' => Category::where('department', 'birthday_wishes')->get(),
        ]);
    }

    public function update(Request $request, BirthdayMessage $birthdayMessage)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: $birthdayMessage->slug;
        $data['status'] = $this->sanitizeStatus($request, $data['status']);

        $birthdayMessage->update($data);
        AuditLog::record($request->user(), 'updated', 'birthday_message', $birthdayMessage->id);

        return redirect()->route('admin.birthday-messages.index')->with('status', 'Saved.');
    }

    public function destroy(Request $request, BirthdayMessage $birthdayMessage)
    {
        AuditLog::record($request->user(), 'deleted', 'birthday_message', $birthdayMessage->id);
        $birthdayMessage->delete();

        return redirect()->route('admin.birthday-messages.index')->with('status', 'Deleted.');
    }

    public function publish(Request $request, BirthdayMessage $birthdayMessage)
    {
        $this->publishModel($request, $birthdayMessage, 'birthday_message');

        return back()->with('status', 'Published.');
    }

    public function unpublish(Request $request, BirthdayMessage $birthdayMessage)
    {
        $this->unpublishModel($request, $birthdayMessage, 'birthday_message');

        return back()->with('status', 'Unpublished.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'relationship' => ['required', 'string', 'max:60'],
            'tone' => ['required', 'string', 'max:60'],
            'language' => ['required', 'in:en,ur'],
            'body' => ['required', 'string'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,in_review,published'],
        ]);
    }
}
