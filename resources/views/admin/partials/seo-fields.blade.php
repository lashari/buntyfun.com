@php
    $m = $item->exists ? $item->meta : null;
    $faqText = collect($m?->faq ?? [])->map(fn ($f) => $f['q'].' || '.$f['a'])->implode("\n");
@endphp
<details class="rounded-lg border border-slate-200 bg-white p-4" @if ($m && ($m->seo_title || $m->publish_at)) open @endif>
    <summary class="cursor-pointer text-sm font-semibold text-slate-700">SEO &amp; scheduling</summary>
    <div class="mt-4 space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700">SEO title <span class="text-slate-400">(max 60; blank = automatic)</span></label>
            <input type="text" name="meta_seo_title" maxlength="70" value="{{ old('meta_seo_title', $m?->seo_title) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Meta description <span class="text-slate-400">(120-155 chars)</span></label>
            <textarea name="meta_seo_description" rows="2" maxlength="170" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('meta_seo_description', $m?->seo_description) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Social image URL <span class="text-slate-400">(optional)</span></label>
            <input type="url" name="meta_og_image" value="{{ old('meta_og_image', $m?->og_image) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">FAQ <span class="text-slate-400">(one per line: <code>Question || Answer</code>; shown on the page + FAQ schema)</span></label>
            <textarea name="meta_faq" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs">{{ old('meta_faq', $faqText) }}</textarea>
        </div>
        <div class="flex flex-wrap gap-5 text-sm">
            <label><input type="checkbox" name="meta_is_featured" value="1" @checked(old('meta_is_featured', $m?->is_featured))> Featured</label>
            <label><input type="checkbox" name="meta_is_trending" value="1" @checked(old('meta_is_trending', $m?->is_trending))> Trending</label>
            <label><input type="checkbox" name="meta_noindex" value="1" @checked(old('meta_noindex', $m?->noindex))> Hide from Google (noindex, not in sitemap)</label>
        </div>
        @if (auth()->user()->canPublish())
            <div>
                <label class="block text-sm font-medium text-slate-700">Publish at <span class="text-slate-400">(future time = kept in review, published automatically then)</span></label>
                <input type="datetime-local" name="meta_publish_at" value="{{ old('meta_publish_at', $m?->publish_at?->format('Y-m-d\TH:i')) }}" class="mt-1 rounded-lg border border-slate-300 px-3 py-2">
            </div>
        @endif
    </div>
</details>
