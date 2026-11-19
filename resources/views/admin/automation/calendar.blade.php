<x-admin-layout title="Publishing calendar">
    <a href="{{ route('admin.automation.index') }}" class="text-sm text-slate-500 hover:underline">← Automation</a>
    <h1 class="mt-2 text-2xl font-bold">Publishing calendar</h1>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div>
            <h2 class="font-semibold">Scheduled</h2>
            @forelse ($scheduled as $day => $items)
                <div class="mt-3 rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-sm font-semibold">{{ \Illuminate\Support\Carbon::parse($day)->format('D m-d-Y') }}</p>
                    <ul class="mt-2 space-y-1 text-sm">
                        @foreach ($items as $m)
                            <li><span class="text-slate-500">{{ $m->publish_at->format('H:i') }}</span> · {{ $m->metable_type }} #{{ $m->metable_id }} —
                                {{ Str::limit($m->metable->title ?? $m->metable->name_en ?? $m->metable->body ?? '', 70) }}</li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="mt-3 text-sm text-slate-500">Nothing scheduled. Schedule from AI Studio or the "SEO &amp; scheduling" box on any item.</p>
            @endforelse
        </div>
        <div>
            <h2 class="font-semibold">Social posts scheduled</h2>
            @forelse ($social as $day => $items)
                <div class="mt-3 rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-sm font-semibold">{{ \Illuminate\Support\Carbon::parse($day)->format('D m-d-Y') }}</p>
                    <ul class="mt-2 space-y-1 text-sm">
                        @foreach ($items as $p)
                            <li><span class="text-slate-500">{{ $p->scheduled_at->format('H:i') }}</span> · {{ ucfirst($p->platform) }} ({{ $p->status }}) — {{ Str::limit($p->caption, 70) }}</li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="mt-3 text-sm text-slate-500">No social posts scheduled. <a href="{{ route('admin.social.create') }}" class="text-violet-700 hover:underline">New post</a></p>
            @endforelse
        </div>
        <div>
            <h2 class="font-semibold">Published (last 14 days)</h2>
            @forelse ($published as $day => $logs)
                <div class="mt-3 rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-sm font-semibold">{{ \Illuminate\Support\Carbon::parse($day)->format('D m-d-Y') }} · {{ $logs->count() }}</p>
                    <ul class="mt-2 space-y-1 text-xs text-slate-600">
                        @foreach ($logs as $l)
                            <li>{{ $l->created_at->format('H:i') }} {{ $l->entity_type }} #{{ $l->entity_id }} ({{ str_starts_with($l->action, 'auto:') ? 'automatic' : 'by hand' }})</li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="mt-3 text-sm text-slate-500">Nothing published in the last 14 days.</p>
            @endforelse
        </div>
    </div>
</x-admin-layout>
