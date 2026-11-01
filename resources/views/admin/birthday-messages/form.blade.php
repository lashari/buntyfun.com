<x-admin-layout :title="$message->exists ? 'Edit Wish' : 'New Wish'">
    <h1 class="text-2xl font-bold">{{ $message->exists ? 'Edit Wish' : 'New Wish' }}</h1>

    <form method="POST" action="{{ $message->exists ? route('admin.birthday-messages.update', $message) : route('admin.birthday-messages.store') }}" class="mt-6 max-w-lg space-y-4">
        @csrf
        @if ($message->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-slate-700">Category</label>
            <select name="category_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($message->category_id === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Relationship (e.g. <code>best_friend</code>, <code>mother</code>)</label>
            <input type="text" name="relationship" value="{{ old('relationship', $message->relationship) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Tone (e.g. <code>funny</code>, <code>emotional</code>)</label>
            <input type="text" name="tone" value="{{ old('tone', $message->tone) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Language</label>
            <select name="language" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="en" @selected(($message->language ?? 'en') === 'en')>English</option>
                <option value="ur" @selected($message->language === 'ur')>Urdu</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Message</label>
            <textarea name="body" rows="4" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('body', $message->body) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="draft" @selected(($message->status ?? 'draft') === 'draft')>Draft</option>
                <option value="in_review" @selected($message->status === 'in_review')>In review</option>
                <option value="published" @selected($message->status === 'published')>Published</option>
            </select>
        </div>

        <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2 font-semibold text-white hover:bg-violet-700">Save</button>
    </form>
</x-admin-layout>
