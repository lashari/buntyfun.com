<x-admin-layout title="Dashboard">
    <h1 class="text-2xl font-bold">Dashboard</h1>

    <div class="mt-6 grid gap-4 sm:grid-cols-4">
        @foreach ($counts as $key => $count)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">{{ Str::headline($key) }}</p>
                <p class="mt-1 text-2xl font-bold">{{ $count['published'] }} <span class="text-sm font-normal text-slate-400">/ {{ $count['total'] }} total</span></p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Content views (all-time)</p>
            <p class="mt-1 text-2xl font-bold">{{ $views }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Share events (all-time)</p>
            <p class="mt-1 text-2xl font-bold">{{ $shares }}</p>
        </div>
    </div>
</x-admin-layout>
