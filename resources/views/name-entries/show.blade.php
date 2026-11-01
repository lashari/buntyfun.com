<x-layout :title="$name->name_en.' — Meaning — BuntyFun'" :description="Str::limit($name->meaning, 150)">
    <div class="mx-auto max-w-xl px-4 py-12">
        <h1 class="text-3xl font-bold">{{ $name->name_en }}</h1>
        @if ($name->name_ar)
            <p class="mt-1 text-2xl text-slate-600" dir="rtl">{{ $name->name_ar }}</p>
        @endif
        <p class="mt-4 text-xs uppercase tracking-wide text-slate-500">
            {{ Str::headline($name->gender) }} @if ($name->origin) · {{ $name->origin }} @endif
        </p>
        <p class="mt-4 text-lg leading-relaxed">{{ $name->meaning }}</p>

        <p class="mt-6 text-xs text-slate-500">Source: {{ $name->source_ref }}</p>

        @if ($related->isNotEmpty())
            <div class="mt-10">
                <h2 class="text-lg font-semibold">Related names</h2>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    @foreach ($related as $r)
                        <a href="{{ route('name-entries.show', $r) }}" class="rounded-lg border border-slate-200 bg-white p-3 text-sm hover:shadow-sm">
                            {{ $r->name_en }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <a href="{{ route('name-entries.index') }}" class="mt-8 block text-sm text-slate-500 hover:underline">Browse all names</a>
    </div>
</x-layout>
