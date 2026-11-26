<x-layout :title="$page['title']" :description="$page['description']"
          :noindex="! $indexable" :urdu-font="true"
          :breadcrumbs="['اردو' => route('urdu-content.index'), $page['subtitle'] => null]">
    <div class="mx-auto max-w-3xl px-4 py-10"
         x-data="{
             copiedId: null,
             track(id, channel) {
                 fetch('{{ route('share-events.store') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                     body: JSON.stringify({ content_type: 'urdu_content', content_id: id, channel }) });
             },
             copy(id, text) { navigator.clipboard.writeText(text); this.copiedId = id; this.track(id, 'copy_link'); setTimeout(() => this.copiedId = null, 2000); },
         }">
        <h1 class="font-urdu text-4xl font-bold leading-loose" dir="rtl" lang="ur">{{ $page['h1'] }}</h1>
        <p class="mt-1 text-lg font-semibold text-slate-700">{{ $page['subtitle'] }}</p>
        <p class="mt-4 leading-relaxed text-slate-700">{{ $page['intro'] }}</p>
        <p class="mt-3 font-urdu text-lg leading-loose text-slate-700" dir="rtl" lang="ur">{{ $page['intro_ur'] }}</p>
        <p class="mt-3 text-sm text-slate-500">{{ $count }} lines. Tap <strong>Copy</strong> or <strong>WhatsApp</strong> on any of them.</p>

        @if ($sections->count() > 1)
            <nav class="mt-6 flex flex-wrap gap-2" aria-label="Jump to">
                @foreach ($sections as $key => $items)
                    <a href="#{{ $key }}" class="rounded-full border border-slate-200 bg-white px-3 py-1 text-sm hover:border-brand-300"><span class="font-urdu" lang="ur">{{ $page['sections'][$key][0] }}</span> · {{ $page['sections'][$key][1] }}</a>
                @endforeach
            </nav>
        @endif

        @foreach ($sections->all() + ($more->isNotEmpty() ? ['more' => $more] : []) as $key => $items)
            @php([$headingUr, $headingEn] = $key === 'more' ? ['مزید', 'More'] : $page['sections'][$key])
            <section class="mt-10" id="{{ $key }}">
                <h2 class="text-xl font-bold"><span class="font-urdu" dir="rtl" lang="ur">{{ $headingUr }}</span> <span class="text-base font-semibold text-slate-500">· {{ $headingEn }}</span></h2>
                <div class="mt-4 space-y-3">
                    @foreach ($items as $item)
                        <div class="rounded-xl border border-slate-200 bg-white p-5">
                            <p class="font-urdu text-lg leading-loose text-slate-800" dir="rtl" lang="ur">{{ $item->body }}</p>
                            <x-speak :text="$item->body" lang="ur" class="mt-2" />
                            <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                                <button type="button" class="rounded-full border border-slate-300 px-3 py-1 hover:bg-slate-50" @click="copy({{ $item->id }}, @js($item->body))">
                                    <span x-show="copiedId !== {{ $item->id }}">Copy</span><span x-show="copiedId === {{ $item->id }}" x-cloak>Copied!</span>
                                </button>
                                <a href="https://wa.me/?text={{ rawurlencode($item->body) }}" target="_blank" rel="noopener" @click="track({{ $item->id }}, 'whatsapp')" class="rounded-full border border-slate-300 px-3 py-1 hover:bg-slate-50">WhatsApp</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        @if ($sections->isEmpty() && $more->isEmpty())
            <p class="mt-8 text-slate-500">Coming soon.</p>
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
            <h2 class="text-lg font-bold">More in Urdu</h2>
            <div class="mt-3 flex flex-wrap gap-2 text-sm">
                @foreach ($others as $otherUrl => $other)
                    <a href="{{ route('urdu-content.page', $otherUrl) }}" class="rounded-full border border-slate-300 px-3 py-1 text-slate-700 hover:border-brand-400"><span class="font-urdu" lang="ur">{{ $other['h1'] }}</span></a>
                @endforeach
                <a href="{{ route('birthday-messages.urdu') }}" class="rounded-full border border-slate-300 px-3 py-1 text-slate-700 hover:border-brand-400">Urdu birthday wishes</a>
                <a href="{{ route('urdu-content.index') }}" class="rounded-full border border-slate-300 px-3 py-1 text-slate-700 hover:border-brand-400">All Urdu</a>
            </div>
        </section>
    </div>
</x-layout>
