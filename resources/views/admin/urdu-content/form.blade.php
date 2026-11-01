<x-admin-layout :title="$item->exists ? 'Edit Urdu Content' : 'New Urdu Content'">
    <h1 class="text-2xl font-bold">{{ $item->exists ? 'Edit Urdu Content' : 'New Urdu Content' }}</h1>
    <p class="mt-1 text-sm text-slate-500">Do not publish without a fluent/native Urdu speaker's review for naturalness and tone.</p>

    <form method="POST" action="{{ $item->exists ? route('admin.urdu-content.update', $item) : route('admin.urdu-content.store') }}" class="mt-6 max-w-lg space-y-4">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-slate-700">Category (e.g. greetings, jokes, quotes, inspirational, love, friendship, festival)</label>
            <input type="text" name="category" value="{{ old('category', $item->category) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Body (Urdu)</label>
            <textarea name="body" rows="4" dir="rtl" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-lg">{{ old('body', $item->body) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="draft" @selected(($item->status ?? 'draft') === 'draft')>Draft</option>
                <option value="in_review" @selected($item->status === 'in_review')>In review</option>
                <option value="published" @selected($item->status === 'published')>Published</option>
            </select>
        </div>

        <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2 font-semibold text-white hover:bg-violet-700">Save</button>
    </form>
</x-admin-layout>
