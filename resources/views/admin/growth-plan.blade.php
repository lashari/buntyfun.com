@php
    $badge = [
        'done' => ['Done', 'bg-emerald-100 text-emerald-700'],
        'doing' => ['Ongoing', 'bg-sky-100 text-sky-700'],
        'todo' => ['Pending', 'bg-amber-100 text-amber-800'],
        'needs_you' => ['Needs you', 'bg-purple-100 text-purple-700'],
    ];
    $pct = $totals['all'] ? round($totals['done'] / $totals['all'] * 100) : 0;
@endphp
<x-admin-layout title="Growth Plan">
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold">Growth Plan</h1>
            <p class="text-sm text-slate-500">What is done, what is pending and when. Plan updated {{ $updated }}.</p>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
            <span class="font-semibold">{{ $totals['done'] }} of {{ $totals['all'] }} tasks done ({{ $pct }}%)</span>
            <span class="flex flex-wrap gap-3 text-slate-600">
                <span>Pending: <b>{{ $totals['todo'] }}</b></span>
                <span>Needs you: <b>{{ $totals['needs_you'] }}</b></span>
                <span>Ongoing: <b>{{ $totals['doing'] }}</b></span>
                <span class="{{ $totals['overdue'] ? 'text-red-600' : '' }}">Overdue: <b>{{ $totals['overdue'] }}</b></span>
            </span>
        </div>
        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
            <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $pct }}%"></div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="font-semibold">Next up</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse ($next as $item)
                    <li class="flex gap-3">
                        <span class="w-24 shrink-0 {{ $item['overdue'] ? 'font-semibold text-red-600' : 'text-slate-500' }}">{{ $item['when'] }}</span>
                        <span>{{ $item['task'] }} <span class="text-slate-400">· {{ $item['who'] }}</span></span>
                    </li>
                @empty
                    <li class="text-slate-500">Nothing pending.</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="font-semibold">Content on the site now</h2>
            <table class="mt-3 w-full text-sm">
                <thead class="text-left text-xs uppercase text-slate-400">
                    <tr><th class="py-1">Type</th><th class="py-1 text-right">Live</th><th class="py-1 text-right">In review / hidden</th></tr>
                </thead>
                <tbody>
                    @foreach ($content as $label => $c)
                        <tr class="border-t border-slate-100">
                            <td class="py-1">{{ $label }}</td>
                            <td class="py-1 text-right font-semibold">{{ $c['published'] }}</td>
                            <td class="py-1 text-right text-slate-500">{{ $c['waiting'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @foreach ($phases as $phase)
        @php $done = collect($phase['items'])->where('status', 'done')->count(); @endphp
        <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                <h2 class="font-semibold">{{ $phase['title'] }}</h2>
                <span class="text-xs text-slate-500">{{ $done }}/{{ count($phase['items']) }} done</span>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-400">
                    <tr>
                        <th class="px-5 py-2">Task</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Who</th>
                        <th class="px-3 py-2">When</th>
                        <th class="px-5 py-2">Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($phase['items'] as $item)
                        @php [$label, $cls] = $badge[$item['status']] ?? [$item['status'], 'bg-slate-100 text-slate-600']; @endphp
                        <tr class="border-t border-slate-100 align-top">
                            <td class="px-5 py-2 {{ $item['status'] === 'done' ? 'text-slate-500' : '' }}">{{ $item['task'] }}</td>
                            <td class="px-3 py-2"><span class="whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium {{ $cls }}">{{ $label }}</span></td>
                            <td class="px-3 py-2 whitespace-nowrap">{{ $item['who'] }}</td>
                            <td class="px-3 py-2 whitespace-nowrap {{ $item['overdue'] ? 'font-semibold text-red-600' : '' }}">{{ $item['when'] }}@if ($item['overdue']) <span class="text-xs">(overdue)</span>@endif</td>
                            <td class="px-5 py-2 text-slate-500">{{ $item['note'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</x-admin-layout>
