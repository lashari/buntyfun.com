<x-admin-layout :title="$name->exists ? 'Edit Name' : 'New Name'">
    <h1 class="text-2xl font-bold">{{ $name->exists ? 'Edit Name' : 'New Name' }}</h1>
    <p class="mt-1 text-sm text-slate-500">Accuracy matters here — do not invent meanings, Arabic spellings, or religious significance. <code>source_ref</code> is required and checked before publish.</p>

    <form method="POST" action="{{ $name->exists ? route('admin.name-entries.update', $name) : route('admin.name-entries.store') }}" class="mt-6 max-w-lg space-y-4">
        @csrf
        @if ($name->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-slate-700">Name (English)</label>
            <input type="text" name="name_en" value="{{ old('name_en', $name->name_en) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Name (Arabic)</label>
            <input type="text" name="name_ar" value="{{ old('name_ar', $name->name_ar) }}" dir="rtl" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Name (Urdu)</label>
            <input type="text" name="name_ur" value="{{ old('name_ur', $name->name_ur) }}" dir="rtl" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Gender</label>
            <select name="gender" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="male" @selected($name->gender === 'male')>Male</option>
                <option value="female" @selected($name->gender === 'female')>Female</option>
                <option value="unisex" @selected($name->gender === 'unisex')>Unisex</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Meaning</label>
            <textarea name="meaning" rows="3" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('meaning', $name->meaning) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Origin</label>
            <input type="text" name="origin" value="{{ old('origin', $name->origin) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Source (required — a real reference, not a placeholder, before publishing)</label>
            <input type="text" name="source_ref" value="{{ old('source_ref', $name->source_ref) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="draft" @selected(($name->status ?? 'draft') === 'draft')>Draft</option>
                <option value="in_review" @selected($name->status === 'in_review')>In review</option>
                <option value="published" @selected($name->status === 'published')>Published</option>
            </select>
        </div>

        <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2 font-semibold text-white hover:bg-violet-700">Save</button>
    </form>
</x-admin-layout>
