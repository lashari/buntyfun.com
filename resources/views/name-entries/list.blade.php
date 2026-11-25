<x-layout :title="$def['title'].' with Meanings — Arabic & Urdu | BuntyFun'" :description="Str::limit($def['intro'], 150)"
          :breadcrumbs="['Islamic Names' => route('name-entries.index'), $def['title'] => null]"
          :noindex="$names->isEmpty() || request()->has('page')" :player="true">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ $def['title'] }}</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">{{ $def['intro'] }}</p>
        @include('name-entries.partials.lists-nav', ['active' => $list])
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($names as $name)
                @include('name-entries.partials.card', ['name' => $name])
            @empty
                <p class="text-sm text-slate-500">Names for this list are being checked by our editors and will appear here soon.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $names->links() }}</div>
    </div>
</x-layout>
