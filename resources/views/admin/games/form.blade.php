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

        <fieldset>
            <legend class="block text-sm font-medium text-slate-700">Who is it for? (filters on /games)</legend>
            @php $aud = old('audience', $game->exists ? \App\Content\OssGames::audienceOf($game) : ['family']); @endphp
            <div class="mt-1 flex flex-wrap gap-4 text-sm">
                @foreach (\App\Content\OssGames::AUDIENCES as $key => $label)
                    <label class="inline-flex items-center gap-1"><input type="checkbox" name="audience[]" value="{{ $key }}" @checked(in_array($key, $aud, true))> {{ $label }}</label>
                @endforeach
            </div>
        </fieldset>

        <div>
            <label class="block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="draft" @selected(($game->status ?? 'draft') === 'draft')>Draft</option>
                <option value="in_review" @selected($game->status === 'in_review')>In review</option>
                <option value="published" @selected($game->status === 'published')>Published</option>
            </select>
        </div>
        @include('admin.partials.seo-fields', ['item' => $game])


        <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 font-semibold text-white hover:bg-brand-700">Save</button>
    </form>
</x-admin-layout>
