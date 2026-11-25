<div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md">
    <a href="{{ route('name-entries.show', $name) }}" class="min-w-0 flex-1">
        <span class="flex items-baseline justify-between gap-3">
            <span class="font-semibold">{{ $name->name_en }}</span>
            @if ($name->name_ar)<span class="text-xl" dir="rtl" lang="ar">{{ $name->name_ar }}</span>@endif
        </span>
        <span class="mt-1 block text-sm text-slate-500">{{ Str::limit($name->meaning, 80) }}</span>
        @if ($name->quranic_ref)<span class="mt-2 inline-block rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">{{ $name->quranic_ref }}</span>@endif
    </a>
    @include('name-entries.partials.play', ['name' => $name])
</div>
