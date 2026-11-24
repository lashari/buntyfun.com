<x-admin-layout title="Daily Schedule">
    <h1 class="text-2xl font-bold">Daily Schedule</h1>
    <p class="mt-1 text-sm text-slate-600">What <a href="{{ route('daily') }}" class="underline" target="_blank">/daily</a> and the home page feature each day. Choose an item to pin it (📌); choose "Auto" to let the system pick from published content. Daily Trivia always uses 5 approved Question Bank questions, picked by date.</p>
    @if (session('status'))<p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-800">{{ session('status') }}</p>@endif
    <div class="mt-6 overflow-x-auto">
        <table class="w-full min-w-[900px] rounded-xl border border-slate-200 bg-white text-sm">
            <thead class="bg-slate-50 text-left text-slate-500"><tr><th class="px-3 py-2">Date</th>@foreach (\App\Models\DailyFeature::TYPES as $label)<th class="px-3 py-2">{{ $label }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($days as $d)
                    @php $key = $d->toDateString(); @endphp
                    <tr>
                        <td class="whitespace-nowrap px-3 py-2 font-medium">{{ $d->format('m-d-Y') }}<br><span class="text-xs text-slate-500">{{ $d->format('l') }}</span></td>
                        @foreach (array_keys(\App\Models\DailyFeature::TYPES) as $type)
                            @php $row = $rows[$key.'|'.$type] ?? null; @endphp
                            <td class="px-3 py-2">
                                <form method="POST" action="{{ route('admin.daily.update') }}" class="flex items-center gap-1">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $key }}"><input type="hidden" name="type" value="{{ $type }}">
                                    <select name="item_id" onchange="this.form.submit()" class="w-64 rounded border-slate-300 text-xs">
                                        <option value="">Auto: {{ Str::limit($options[$type][$shown[$key][$type]] ?? 'nothing published', 50) }}</option>
                                        @foreach ($options[$type] as $id => $label)<option value="{{ $id }}" @selected($row?->pinned && $row->item_id === $id)>{{ $label }}</option>@endforeach
                                    </select>
                                    @if ($row?->pinned)<span title="Pinned by an admin">📌</span>@endif
                                </form>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin-layout>
