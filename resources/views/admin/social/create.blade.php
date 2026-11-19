<x-admin-layout title="New social post">
    <a href="{{ route('admin.social.index') }}" class="text-sm text-slate-500 hover:underline">← Social</a>
    <h1 class="mt-2 text-2xl font-bold">New social post</h1>
    @if ($prefill)<p class="mt-1 text-sm text-slate-500">From: {{ $prefill['title'] }}</p>@endif

    @if ($accounts->isEmpty())
        <div class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">No account connected yet. A super admin must click "Connect Facebook &amp; Instagram" on the Social page first.</div>
    @endif

    <form method="POST" action="{{ route('admin.social.store') }}" enctype="multipart/form-data" class="mt-6 max-w-2xl space-y-4">
        @csrf
        <input type="hidden" name="source_type" value="{{ $sourceType }}">
        <input type="hidden" name="source_id" value="{{ $sourceId }}">

        <div>
            <p class="text-sm font-medium text-slate-700">Post to <span class="text-slate-400">(only BuntyFun accounts are ticked by default)</span></p>
            <div class="mt-1 flex flex-wrap gap-4 text-sm">
                @foreach ($accounts as $a)
                    <label class="flex items-center gap-2"><input type="checkbox" name="accounts[]" value="{{ $a->id }}" @checked(in_array($a->id, old('accounts', $accounts->filter(fn ($x) => str_contains(strtolower($x->name.' '.$x->username), 'bunty'))->pluck('id')->all())))> {{ ucfirst($a->platform) }} · {{ $a->label() }}</label>
                @endforeach
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Caption</label>
            <textarea name="caption" rows="6" required maxlength="2000" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('caption', $prefill['caption'] ?? '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Link <span class="text-slate-400">(UTM tags are added per post; Instagram shows links as plain text)</span></label>
            <input type="url" name="link" value="{{ old('link', $prefill['link'] ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">Image upload <span class="text-slate-400">(JPG/PNG, needed for Instagram)</span></label>
                <input type="file" name="image" accept="image/jpeg,image/png" class="mt-1 w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">…or image URL</label>
                <input type="url" name="image_url" value="{{ old('image_url', $prefill['image_url'] ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Publish at <span class="text-slate-400">(server time {{ config('app.timezone') }}; blank = right after approval)</span></label>
            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="mt-1 rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div class="flex gap-3">
            @if (auth()->user()->canPublish())
                <button name="action" value="approve" class="rounded-lg bg-violet-600 px-5 py-2 font-semibold text-white hover:bg-violet-700">Approve &amp; schedule</button>
            @endif
            <button name="action" value="draft" class="rounded-lg border border-slate-300 px-5 py-2 hover:bg-slate-50">Save draft for approval</button>
        </div>
    </form>
</x-admin-layout>
