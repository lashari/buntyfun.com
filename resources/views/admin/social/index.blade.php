<x-admin-layout title="Social">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Social Media Manager</h1>
            <p class="mt-1 text-sm text-slate-500">Facebook Page + Instagram through Meta's official API. Every post needs an admin's approval before it goes out.</p>
        </div>
        <a href="{{ route('admin.social.create') }}" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">New post</a>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold">Connected accounts</h2>
            @if (auth()->user()->isSuperAdmin())
                @if ($configured)
                    <a href="{{ route('admin.social.meta.connect') }}" class="rounded-lg bg-[#1877F2] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">{{ $accounts->isEmpty() ? 'Connect Facebook & Instagram' : 'Reconnect / add Page' }}</a>
                @else
                    <span class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">Add <code>META_APP_SECRET</code> to the server <code>.env</code> to enable Connect.</span>
                @endif
            @endif
        </div>
        <ul class="mt-3 divide-y divide-slate-100 text-sm">
            @forelse ($accounts as $a)
                <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                    <span><strong>{{ ucfirst($a->platform) }}</strong> · {{ $a->label() }}
                        <span class="text-xs text-slate-400">token stored encrypted{{ $a->token_expires_at ? ', expires '.$a->token_expires_at->format('m-d-Y') : ', does not expire' }}</span>
                        @if ($a->last_error)<span class="block text-xs text-red-600">{{ $a->last_error }}</span>@endif
                    </span>
                    @if (auth()->user()->isSuperAdmin())
                        <form method="POST" action="{{ route('admin.social.accounts.disconnect', $a) }}" onsubmit="return confirm('Disconnect {{ $a->label() }}? Waiting posts for it will be cancelled.')">@csrf @method('DELETE')
                            <button class="text-xs text-red-600 hover:underline">Disconnect</button></form>
                    @endif
                </li>
            @empty
                <li class="py-2 text-slate-500">Nothing connected yet.</li>
            @endforelse
        </ul>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-4">
        @foreach (['Published (30 d)' => $stats['published_30d'], 'Approved / scheduled' => $stats['scheduled'], 'Drafts to approve' => $stats['drafts'], 'Failed' => $stats['failed']] as $label => $v)
            <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">{{ $label }}</p><p class="mt-1 text-xl font-bold">{{ $v }}</p></div>
        @endforeach
    </div>
    <p class="mt-2 text-xs text-slate-500">Clicks: every link carries <code>utm_source=facebook|instagram&amp;utm_medium=social&amp;utm_campaign=bf_social&amp;utm_content=post&lt;id&gt;</code>; see GA4 → Traffic acquisition → Session source / medium.</p>

    <div class="mt-6 flex flex-wrap gap-2 text-sm">
        @foreach (['open' => 'Open', 'published' => 'Published', 'cancelled' => 'Cancelled', 'all' => 'All'] as $s => $l)
            <a href="{{ route('admin.social.index', ['status' => $s]) }}" class="rounded-full border px-3 py-1 {{ $status === $s ? 'border-violet-600 bg-violet-600 text-white' : 'border-slate-300 text-slate-600' }}">{{ $l }}</a>
        @endforeach
    </div>

    <table class="mt-3 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr><th class="px-3 py-2">Post</th><th class="px-3 py-2">Where</th><th class="px-3 py-2">When</th><th class="px-3 py-2">Status</th><th class="px-3 py-2"></th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($posts as $p)
                <tr class="align-top">
                    <td class="px-3 py-2">
                        <div class="flex gap-3">
                            @if ($p->image_url)<img src="{{ $p->image_url }}" alt="" class="h-12 w-12 shrink-0 rounded object-cover">@endif
                            <div>
                                <p class="whitespace-pre-line">{{ Str::limit($p->caption, 140) }}</p>
                                @if ($p->link)<p class="mt-1 truncate text-xs text-slate-400">{{ Str::limit($p->link, 80) }}</p>@endif
                                @if ($p->error)<p class="mt-1 text-xs text-red-600">{{ $p->error }}</p>@endif
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ ucfirst($p->platform) }}<div class="text-xs text-slate-400">{{ $p->account?->label() ?? 'disconnected' }}</div></td>
                    <td class="px-3 py-2 whitespace-nowrap text-xs">
                        {{ $p->published_at?->format('m-d-Y H:i') ?? ($p->scheduled_at?->format('m-d-Y H:i') ?? 'as soon as approved') }}
                    </td>
                    <td class="px-3 py-2">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ ['published' => 'bg-emerald-100 text-emerald-700', 'failed' => 'bg-red-100 text-red-700', 'approved' => 'bg-sky-100 text-sky-700', 'draft' => 'bg-amber-100 text-amber-800'][$p->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $p->status }}</span>
                        @if ($p->permalink)<a href="{{ $p->permalink }}" target="_blank" rel="noopener" class="ml-1 text-xs text-violet-700 hover:underline">view</a>@endif
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-right text-xs">
                        @if (auth()->user()->canPublish())
                            @if ($p->status === 'draft')
                                <form method="POST" action="{{ route('admin.social.approve', $p) }}" class="inline">@csrf<button class="text-emerald-700 hover:underline">Approve</button></form>
                            @endif
                            @if (in_array($p->status, ['draft', 'approved']))
                                <form method="POST" action="{{ route('admin.social.publish-now', $p) }}" class="ml-2 inline">@csrf<button class="text-violet-700 hover:underline">Post now</button></form>
                            @endif
                            @if ($p->status === 'failed')
                                <form method="POST" action="{{ route('admin.social.retry', $p) }}" class="inline">@csrf<button class="text-violet-700 hover:underline">Retry</button></form>
                            @endif
                        @endif
                        @if (in_array($p->status, ['draft', 'approved', 'failed']))
                            <form method="POST" action="{{ route('admin.social.cancel', $p) }}" class="ml-2 inline">@csrf<button class="text-slate-500 hover:underline">Cancel</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">No posts here.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $posts->links() }}</div>

    <h2 class="mt-8 text-lg font-bold">Worth posting (most viewed + shared, last 30 days)</h2>
    <table class="mt-3 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
        <tbody class="divide-y divide-slate-100">
            @forelse ($top as $t)
                <tr>
                    <td class="px-3 py-2">{{ $t['title'] }} <span class="text-xs text-slate-400">{{ $t['type'] }}</span></td>
                    <td class="px-3 py-2 text-xs text-slate-500">{{ $t['views'] }} views · {{ $t['shares'] }} shares · posted {{ $t['posted'] }}×</td>
                    <td class="px-3 py-2 text-right"><a href="{{ route('admin.social.create', ['type' => $t['type'], 'id' => $t['id']]) }}" class="text-xs text-violet-700 hover:underline">Create post</a></td>
                </tr>
            @empty
                <tr><td class="px-3 py-4 text-slate-500">No view data yet. It fills up as visitors open pages.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-admin-layout>
