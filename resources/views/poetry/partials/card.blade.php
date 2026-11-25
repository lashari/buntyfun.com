<a href="{{ route('poetry.show', [$poem->poet->slug, $poem->slug]) }}" class="block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md">
    <span class="font-urdu block text-right text-lg leading-[2.2] text-slate-800" dir="rtl" lang="ur">{{ Str::limit($poem->firstLines(2), 140) }}</span>
    <span class="mt-2 flex items-center justify-between text-xs text-slate-500">
        <span>{{ $poem->poet->name_en }} · {{ \App\Models\Poem::FORMS[$poem->form][0] ?? $poem->form }}</span>
        <span class="font-urdu" dir="rtl" lang="ur">{{ $poem->poet->name_ur }}</span>
    </span>
</a>
