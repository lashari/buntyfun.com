<x-layout :title="$def['title'].' with Meanings — Arabic & Urdu | BuntyFun'" :description="Str::limit($def['intro'], 150)"
          :breadcrumbs="['Islamic Names' => route('name-entries.index'), $def['title'] => null]"
          :noindex="$names->isEmpty()">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ $def['title'] }}</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">{{ $def['intro'] }}</p>
        @include('name-entries.partials.lists-nav', ['active' => $list])
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($names as $name)
                <a href="{{ route('name-entries.show', $name) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                    <span class="flex items-baseline justify-between gap-3">
                        <span class="font-semibold">{{ $name->name_en }}</span>
                        @if ($name->name_ar)<span class="text-xl" dir="rtl" lang="ar">{{ $name->name_ar }}</span>@endif
                    </span>
                    <span class="mt-1 block text-sm text-slate-500">{{ Str::limit($name->meaning, 80) }}</span>
                    @if ($name->quranic_ref)<span class="mt-2 inline-block rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">{{ $name->quranic_ref }}</span>@endif
                </a>
            @empty
                <p class="text-sm text-slate-500">Names for this list are being checked by our editors and will appear here soon.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $names->links() }}</div>
    </div>
</x-layout>
