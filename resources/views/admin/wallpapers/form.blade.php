<x-admin-layout :title="$wallpaper->exists ? 'Edit Wallpaper' : 'Upload Wallpaper'">
    <h1 class="text-2xl font-bold">{{ $wallpaper->exists ? 'Edit Wallpaper' : 'Upload Wallpaper' }}</h1>
    <p class="mt-1 text-sm text-slate-500">Only upload images you have the right to redistribute — original work, public domain, CC0, or properly attributed CC-BY/licensed. Never a copyrighted collection you don't have permission for.</p>

    <form method="POST" action="{{ $wallpaper->exists ? route('admin.wallpapers.update', $wallpaper) : route('admin.wallpapers.store') }}"
          enctype="multipart/form-data" class="mt-6 max-w-lg space-y-4">
        @csrf
        @if ($wallpaper->exists) @method('PUT') @endif

        @if ($wallpaper->exists)
            <img src="{{ $wallpaper->url() }}" alt="{{ $wallpaper->title }}" class="w-full rounded-lg border border-slate-200">
        @endif

        <div>
            <label class="block text-sm font-medium text-slate-700">Image {{ $wallpaper->exists ? '(leave blank to keep current)' : '' }}</label>
            <input type="file" name="image" accept="image/*" {{ $wallpaper->exists ? '' : 'required' }} class="mt-1 w-full text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Title</label>
            <input type="text" name="title" value="{{ old('title', $wallpaper->title) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Category (nature, landscapes, abstract, minimalist, technology, animals, space, seasonal)</label>
            <input type="text" name="category" value="{{ old('category', $wallpaper->category) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Orientation</label>
            <select name="orientation" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="both" @selected(($wallpaper->orientation ?? 'both') === 'both')>Both</option>
                <option value="desktop" @selected($wallpaper->orientation === 'desktop')>Desktop</option>
                <option value="mobile" @selected($wallpaper->orientation === 'mobile')>Mobile</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">License</label>
            <select name="license" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="original" @selected(($wallpaper->license ?? 'original') === 'original')>Original (we made it)</option>
                <option value="public_domain" @selected($wallpaper->license === 'public_domain')>Public domain</option>
                <option value="cc0" @selected($wallpaper->license === 'cc0')>CC0</option>
                <option value="cc_by" @selected($wallpaper->license === 'cc_by')>CC-BY (attribution required)</option>
                <option value="licensed" @selected($wallpaper->license === 'licensed')>Licensed (attribution required)</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Attribution (required for CC-BY / Licensed)</label>
            <input type="text" name="attribution" value="{{ old('attribution', $wallpaper->attribution) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Source URL</label>
            <input type="url" name="source_url" value="{{ old('source_url', $wallpaper->source_url) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="draft" @selected(($wallpaper->status ?? 'draft') === 'draft')>Draft</option>
                <option value="in_review" @selected($wallpaper->status === 'in_review')>In review</option>
                <option value="published" @selected($wallpaper->status === 'published')>Published</option>
            </select>
        </div>

        <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2 font-semibold text-white hover:bg-violet-700">Save</button>
    </form>
</x-admin-layout>
