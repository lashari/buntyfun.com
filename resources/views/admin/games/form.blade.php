<x-admin-layout :title="$game->exists ? 'Edit Game' : 'New Game'">
    <h1 class="text-2xl font-bold">{{ $game->exists ? 'Edit Game' : 'New Game' }}</h1>

    <form method="POST" action="{{ $game->exists ? route('admin.games.update', $game) : route('admin.games.store') }}" class="mt-6 max-w-lg space-y-4">
        @csrf
        @if ($game->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-slate-700">Category</label>
            <select name="category_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($game->category_id === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Title</label>
            <input type="text" name="title" value="{{ old('title', $game->title) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Slug (must match a built-in game view, e.g. <code>tic-tac-toe</code>)</label>
            <input type="text" name="slug" value="{{ old('slug', $game->slug) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="draft" @selected(($game->status ?? 'draft') === 'draft')>Draft</option>
                <option value="in_review" @selected($game->status === 'in_review')>In review</option>
                <option value="published" @selected($game->status === 'published')>Published</option>
            </select>
        </div>

        <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2 font-semibold text-white hover:bg-violet-700">Save</button>
    </form>
</x-admin-layout>
