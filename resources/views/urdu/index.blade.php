<x-layout dir="rtl" lang="ur" title="اردو تفریح — BuntyFun" description="لطیفے، پیغامات اور اقتباسات اردو میں۔">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">اردو تفریح</h1>

        <form method="GET" class="mt-4 flex flex-wrap gap-3">
            <select name="category" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">تمام اقسام</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
        </form>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @forelse ($content as $item)
                <a href="{{ route('urdu-content.show', $item) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                    <p class="text-lg leading-relaxed">{{ Str::limit($item->body, 100) }}</p>
                    <p class="mt-2 text-xs uppercase tracking-wide text-slate-400">{{ $item->category }}</p>
                </a>
            @empty
                <p class="text-sm text-slate-500">ابھی کوئی مواد دستیاب نہیں ہے۔</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $content->links() }}</div>
    </div>
</x-layout>
