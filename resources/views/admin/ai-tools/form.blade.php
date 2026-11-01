<x-admin-layout :title="$tool->exists ? 'Edit AI Tool' : 'New AI Tool'">
    <h1 class="text-2xl font-bold">{{ $tool->exists ? 'Edit AI Tool' : 'New AI Tool' }}</h1>

    <form method="POST" action="{{ $tool->exists ? route('admin.ai-tools.update', $tool) : route('admin.ai-tools.store') }}" class="mt-6 max-w-lg space-y-4">
        @csrf
        @if ($tool->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-slate-700">Title</label>
            <input type="text" name="title" value="{{ old('title', $tool->title) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Slug (optional)</label>
            <input type="text" name="slug" value="{{ old('slug', $tool->slug) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Description (shown on the catalog card)</label>
            <input type="text" name="description" value="{{ old('description', $tool->description) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Prompt template — must include <code>{input}</code> for the visitor's text</label>
            <textarea name="prompt_template" rows="4" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm">{{ old('prompt_template', $tool->prompt_template) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="draft" @selected(($tool->status ?? 'draft') === 'draft')>Draft</option>
                <option value="in_review" @selected($tool->status === 'in_review')>In review</option>
                <option value="published" @selected($tool->status === 'published')>Published</option>
            </select>
        </div>

        <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2 font-semibold text-white hover:bg-violet-700">Save</button>
    </form>
</x-admin-layout>
