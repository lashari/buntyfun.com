<x-admin-layout :title="($item->exists ? 'Edit ' : 'New ').Str::singular($t['label'])">
    <h1 class="text-2xl font-bold">{{ $item->exists ? 'Edit' : 'New' }} {{ Str::singular($t['label']) }}</h1>
    <p class="mt-1 text-sm text-slate-500">Do not invent meanings, Arabic text or references. Every religious item needs a real Quran or hadith reference.</p>
    <form method="POST" action="{{ $item->exists ? route('admin.library.update', [$type, $item->id]) : route('admin.library.store', $type) }}" class="mt-6 max-w-2xl space-y-4" enctype="multipart/form-data">
        @csrf
        @foreach ($t['fields'] as $name => [$label, $input])
            <div>
                <label for="f-{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
                @php $val = old($name, $name === 'ref_slug' && $item->exists ? $item->owner()?->slug : $item->{$name}); $rtl = str_ends_with($input, '-rtl'); @endphp
                @if ($input === 'poet')
                    <select id="f-{{ $name }}" name="{{ $name }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">@foreach ($poets as $id => $n)<option value="{{ $id }}" @selected($val == $id)>{{ $n }}</option>@endforeach</select>
                @elseif (str_starts_with($input, 'select:'))
                    <select id="f-{{ $name }}" name="{{ $name }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                        @foreach (explode(',', substr($input, 7)) as $opt)<option value="{{ $opt }}" @selected((string) $val === $opt)>{{ $opt === '' ? '— none —' : Str::headline($opt) }}</option>@endforeach
                    </select>
                @elseif ($input === 'file')
                    <input id="f-{{ $name }}" type="file" name="{{ $name }}" accept="audio/*" class="mt-1 block w-full text-sm">
                    @if ($item->file_path ?? null)<audio controls preload="none" src="{{ $item->src() }}" class="mt-2 w-full"></audio><p class="text-xs text-slate-500">Current file: {{ $item->file_path }} (upload a new one to replace it)</p>@endif
                    @if (($item->external_url ?? null) && ! ($item->file_path ?? null))<audio controls preload="none" src="{{ $item->external_url }}" class="mt-2 w-full"></audio>@endif
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
        @error('ref_slug')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        @error('upload')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        @if ($type === 'audio')<p class="rounded-lg bg-amber-50 p-3 text-xs text-amber-900">Only publish a recording after listening to all of it. Never upload Quran audio made with voice imitation or AI, and never upload a recording you do not have permission to share.</p>@endif
        <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 font-semibold text-white">Save</button>
        <a href="{{ route('admin.library.index', $type) }}" class="ml-3 text-sm text-slate-500">Cancel</a>
    </form>
</x-admin-layout>
