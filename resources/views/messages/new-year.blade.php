@php
    $year = \App\Content\NewYearWishes::YEAR;
@endphp
<x-layout :title="'New Year Wishes '.$year.' — Messages for Family, Friends & Work | BuntyFun'"
          :description="$count.' New Year '.$year.' wishes for family, friends, love and work — funny, short, with a du\'a and in Urdu. Copy or send on WhatsApp.'"
          :noindex="! $indexable" :urdu-font="true"
          :breadcrumbs="['Messages' => route('messages.index'), 'New Year Wishes '.$year => null]">
    <div class="mx-auto max-w-3xl px-4 py-10"
         x-data="{
             copiedId: null,
             track(id, channel) {
                 fetch('{{ route('share-events.store') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                     body: JSON.stringify({ content_type: 'message', content_id: id, channel }) });
             },
             copy(id, text) { navigator.clipboard.writeText(text); this.copiedId = id; this.track(id, 'copy_link'); setTimeout(() => this.copiedId = null, 2000); },
         }">
        <h1 class="text-3xl font-bold">New Year Wishes {{ $year }}</h1>
        <p class="mt-2 font-urdu text-2xl leading-loose text-orange-700" dir="rtl" lang="ur">نیا سال مبارک</p>
        <p class="mt-4 leading-relaxed text-slate-700">Here are {{ $count }} New Year wishes for {{ $year }}, each written for a particular person: parents, friends, your partner, the office group or a client. Pick a section, tap <strong>Copy</strong> or <strong>WhatsApp</strong>, and add the person's name or a memory from the past year so it sounds like you.</p>

        <nav class="mt-6 flex flex-wrap gap-2" aria-label="Jump to">
            @foreach ($sections as $key => $items)
                <a href="#{{ $key }}" class="rounded-full border border-slate-200 bg-white px-3 py-1 text-sm hover:border-brand-300">{{ \Illuminate\Support\Str::of(\App\Content\NewYearWishes::SECTIONS[$key][0])->replace('New Year wishes ', '')->replace('for ', '')->ucfirst() }}</a>
            @endforeach
        </nav>

        @forelse ($sections as $key => $items)
            @php([$heading, $intro, $lang] = \App\Content\NewYearWishes::SECTIONS[$key])
            <section class="mt-10" id="{{ $key }}">
                <h2 class="text-xl font-bold">{{ $heading }}</h2>
                <p class="mt-1 text-sm text-slate-600">{{ $intro }}</p>
                <div class="mt-4 space-y-3">
                    @foreach ($items as $wish)
                        <div class="rounded-xl border border-slate-200 bg-white p-5">
                            @if ($wish->title)<p class="text-xs font-semibold uppercase tracking-wide text-slate-500 {{ $wish->language === 'ur' ? 'font-urdu normal-case text-sm' : '' }}" @if ($wish->language === 'ur') dir="rtl" lang="ur" @endif>{{ $wish->title }}</p>@endif
                            <p class="mt-1 {{ $wish->language === 'ur' ? 'font-urdu text-lg leading-loose' : 'leading-relaxed' }} text-slate-800" @if ($wish->language === 'ur') dir="rtl" lang="ur" @endif>{{ $wish->body }}</p>
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
        @empty
            <p class="mt-8 text-slate-500">New Year wishes are coming soon.</p>
        @endforelse

        @if ($more->isNotEmpty())
            <section class="mt-10" id="more">
                <h2 class="text-xl font-bold">More New Year wishes</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($more as $wish)
                        <div class="rounded-xl border border-slate-200 bg-white p-5">
                            <p class="leading-relaxed text-slate-800" @if ($wish->language === 'ur') dir="rtl" lang="ur" @endif>{{ $wish->body }}</p>
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
        @endif

        <section class="mt-12 rounded-xl bg-brand-50 p-6 prose-page">
            <h2 class="!mt-0">Tips for sending New Year wishes</h2>
            <ul>
                <li>Add one personal line — a trip you took, a joke from the year, or "thank you for…". A copied wish plus one real sentence beats a long generic one.</li>
                <li>Send to parents and elders before midnight gets busy; networks are slow right at 12:00 and messages can arrive late.</li>
                <li>For colleagues and clients, keep it in working hours on 1 January or the first working day, and skip the jokes.</li>
                <li>Urdu text on WhatsApp reads right-to-left automatically. Send it on its own, without English in the same message, so it doesn't look jumbled.</li>
            </ul>
        </section>
        <p class="mt-8 text-sm text-slate-500">Need something else? Browse all <a href="{{ route('messages.index') }}" class="text-navy-600 underline">messages and quotes</a>, <a href="{{ route('birthday-messages.index') }}" class="text-navy-600 underline">birthday wishes</a>, or <a href="{{ route('urdu-content.index') }}" class="text-navy-600 underline">Urdu greetings</a>.</p>
    </div>
</x-layout>
