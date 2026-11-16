<x-admin-layout :title="$item->exists ? 'Edit Message' : 'New Message'">
    <h1 class="text-2xl font-bold">{{ $item->exists ? 'Edit Message' : 'New Message' }}</h1>

    <form method="POST" action="{{ $item->exists ? route('admin.messages.update', $item) : route('admin.messages.store') }}" class="mt-6 max-w-lg space-y-4">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-slate-700">Category</label>
            <select name="category" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                @foreach (\App\Models\Message::CATEGORIES as $key => [$label, $ads])
                    <option value="{{ $key }}" @selected(old('category', $item->category) === $key)>{{ $label }}{{ $ads ? '' : ' (never shows ads)' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Language</label>
            <select name="language" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="en" @selected(old('language', $item->language ?? 'en') === 'en')>English</option>
                <option value="ur" @selected(old('language', $item->language) === 'ur')>Urdu</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Title <span class="text-slate-400">(optional)</span></label>
            <input type="text" name="title" value="{{ old('title', $item->title) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Message</label>
            <textarea name="body" rows="5" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('body', $item->body) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Slug <span class="text-slate-400">(blank = automatic)</span></label>
            <input type="text" name="slug" value="{{ old('slug', $item->slug) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                @foreach (['draft' => 'Draft', 'in_review' => 'In review', 'published' => 'Published'] as $v => $l)
                    <option value="{{ $v }}" @selected(old('status', $item->status ?? 'draft') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>

        @include('admin.partials.seo-fields', ['item' => $item])

        <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2 font-semibold text-white hover:bg-violet-700">Save</button>
    </form>
</x-admin-layout>
