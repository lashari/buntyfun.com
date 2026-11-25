<x-admin-layout :title="($item->exists ? 'Edit ' : 'New ').Str::singular($t['label'])">
    <h1 class="text-2xl font-bold">{{ $item->exists ? 'Edit' : 'New' }} {{ Str::singular($t['label']) }}</h1>
    <p class="mt-1 text-sm text-slate-500">Do not invent meanings, Arabic text or references. Every religious item needs a real Quran or hadith reference.</p>
    <form method="POST" action="{{ $item->exists ? route('admin.library.update', [$type, $item->id]) : route('admin.library.store', $type) }}" class="mt-6 max-w-2xl space-y-4">
        @csrf
        @foreach ($t['fields'] as $name => [$label, $input])
            <div>
                <label for="f-{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
                @php $val = old($name, $item->{$name}); $rtl = str_ends_with($input, '-rtl'); @endphp
                @if ($input === 'poet')
                    <select id="f-{{ $name }}" name="{{ $name }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">@foreach ($poets as $id => $n)<option value="{{ $id }}" @selected($val == $id)>{{ $n }}</option>@endforeach</select>
                @elseif (str_starts_with($input, 'select:'))
                    <select id="f-{{ $name }}" name="{{ $name }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                        @foreach (explode(',', substr($input, 7)) as $opt)<option value="{{ $opt }}" @selected((string) $val === $opt)>{{ $opt === '' ? '— none —' : Str::headline($opt) }}</option>@endforeach
                    </select>
                @elseif (str_starts_with($input, 'textarea'))
                    <textarea id="f-{{ $name }}" name="{{ $name }}" rows="{{ $name === 'body' ? 16 : 4 }}" @if ($rtl) dir="rtl" @endif class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 {{ $rtl ? 'font-urdu text-lg leading-loose' : '' }}">{{ $val }}</textarea>
                @else
                    <input id="f-{{ $name }}" type="{{ $input === 'number' ? 'number' : 'text' }}" name="{{ $name }}" value="{{ $val }}" @if ($rtl) dir="rtl" @endif class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 {{ $rtl ? 'font-urdu text-lg' : '' }}">
                @endif
            </div>
        @endforeach
        <div>
            <label for="f-status" class="block text-sm font-medium text-slate-700">Status</label>
            <select id="f-status" name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                @foreach (\App\Http\Controllers\Admin\LibraryAdminController::STATUSES as $k => $l)
                    @if ($k !== 'published' || auth()->user()->canPublish())<option value="{{ $k }}" @selected(old('status', $item->status ?? 'in_review') === $k)>{{ $l }}</option>@endif
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 font-semibold text-white">Save</button>
        <a href="{{ route('admin.library.index', $type) }}" class="ml-3 text-sm text-slate-500">Cancel</a>
    </form>
</x-admin-layout>
