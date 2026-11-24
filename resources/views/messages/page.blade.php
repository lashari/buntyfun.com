<x-layout :title="$page['title']" :description="$page['description']"
          :noindex="! $indexable" :ads="$ads" :urdu-font="true"
          :breadcrumbs="['Messages' => route('messages.index'), $page['h1'] => null]">
    <div class="mx-auto max-w-3xl px-4 py-10"
         x-data="{
             copiedId: null,
             track(id, channel) {
                 fetch('{{ route('share-events.store') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                     body: JSON.stringify({ content_type: 'message', content_id: id, channel }) });
             },
             copy(id, text) { navigator.clipboard.writeText(text); this.copiedId = id; this.track(id, 'copy_link'); setTimeout(() => this.copiedId = null, 2000); },
         }">
        <h1 class="text-3xl font-bold">{{ $page['h1'] }}</h1>
        @if ($page['urdu_heading'])
            <p class="mt-2 font-urdu text-2xl leading-loose text-orange-700" dir="rtl" lang="ur">{{ $page['urdu_heading'] }}</p>
        @endif
        <p class="mt-4 leading-relaxed text-slate-700">{{ $page['intro'] }}</p>
        <p class="mt-3 text-sm text-slate-500">{{ $count }} messages. Tap <strong>Copy</strong> or <strong>WhatsApp</strong> on any of them.</p>

        @if ($sections->count() > 1)
            <nav class="mt-6 flex flex-wrap gap-2" aria-label="Jump to">
                @foreach ($sections as $key => $items)
                    <a href="#{{ $key }}" class="rounded-full border border-slate-200 bg-white px-3 py-1 text-sm hover:border-brand-300">{{ $page['sections'][$key][0] }}</a>
                @endforeach
            </nav>
        @endif

        @foreach ($sections->all() + ($more->isNotEmpty() ? ['more' => $more] : []) as $key => $items)
            @php([$heading, $intro] = $key === 'more' ? ['More '.Str::lower($page['h1']), null] : $page['sections'][$key])
            <section class="mt-10" id="{{ $key }}">
                <h2 class="text-xl font-bold">{{ $heading }}</h2>
                @if ($intro)<p class="mt-1 text-sm text-slate-600">{{ $intro }}</p>@endif
                <div class="mt-4 space-y-3">
                    @foreach ($items as $wish)
                        @php($ur = $wish->language === 'ur')
                        <div class="rounded-xl border border-slate-200 bg-white p-5">
                            @if ($wish->title)<p class="text-xs font-semibold uppercase tracking-wide text-slate-500 {{ $ur ? 'font-urdu normal-case text-sm' : '' }}" @if ($ur) dir="rtl" lang="ur" @endif>{{ $wish->title }}</p>@endif
                            <p class="mt-1 {{ $ur ? 'font-urdu text-lg leading-loose' : 'leading-relaxed' }} text-slate-800" @if ($ur) dir="rtl" lang="ur" @endif>{{ $wish->body }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                                <button type="button" class="rounded-full border border-slate-300 px-3 py-1 hover:bg-slate-50" @click="copy({{ $wish->id }}, @js($wish->body))">
                                    <span x-show="copiedId !== {{ $wish->id }}">Copy</span><span x-show="copiedId === {{ $wish->id }}" x-cloak>Copied!</span>
                                </button>
                                <a href="https://wa.me/?text={{ rawurlencode($wish->body) }}" target="_blank" rel="noopener" @click="track({{ $wish->id }}, 'whatsapp')" class="rounded-full border border-slate-300 px-3 py-1 hover:bg-slate-50">WhatsApp</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        @if ($sections->isEmpty() && $more->isEmpty())
            <p class="mt-8 text-slate-500">Messages are coming soon.</p>
        @endif

        <section class="mt-12 rounded-xl bg-brand-50 p-6 prose-page">
            <h2 class="!mt-0">Tips</h2>
            <ul>
                @foreach ($page['tips'] as $tip)
                    <li>{{ $tip }}</li>
                @endforeach
            </ul>
        </section>

        <section class="mt-10">
            <h2 class="text-lg font-bold">More messages</h2>
            <div class="mt-3 flex flex-wrap gap-2 text-sm">
                @foreach ($others as $otherUrl => $label)
                    <a href="{{ route('messages.page', $otherUrl) }}" class="rounded-full border border-slate-300 px-3 py-1 text-slate-700 hover:border-brand-400">{{ $label }}</a>
                @endforeach
                <a href="{{ route('messages.new-year') }}" class="rounded-full border border-slate-300 px-3 py-1 text-slate-700 hover:border-brand-400">New Year Wishes</a>
                <a href="{{ route('birthday-messages.index') }}" class="rounded-full border border-slate-300 px-3 py-1 text-slate-700 hover:border-brand-400">Birthday Wishes</a>
            </div>
        </section>
    </div>
</x-layout>
