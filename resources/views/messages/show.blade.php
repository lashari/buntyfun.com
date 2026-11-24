<x-layout :dir="$message->language === 'ur' ? 'rtl' : 'ltr'" :lang="$message->language"
          :title="($message->title ?: $message->categoryLabel()).' — BuntyFun'"
          :description="Str::limit($message->body, 140).' Copy or share this message.'"
          :meta="$message->meta" :ads="$message->adsAllowed()" :noindex="! config('site.index_single_snippets')"
          :breadcrumbs="['Messages' => route('messages.index'), $message->categoryLabel() => \App\Models\Message::categoryUrl($message->category), ($message->title ?: 'Message') => null]">
    <div class="mx-auto max-w-xl px-4 py-12">
        <p class="text-xs uppercase tracking-wide text-slate-500">{{ $message->categoryLabel() }}</p>
        @if ($message->title)<h1 class="mt-2 text-2xl font-bold">{{ $message->title }}</h1>@endif
        <p class="mt-4 text-xl leading-relaxed">{{ $message->body }}</p>

        <div class="mt-8 flex flex-wrap gap-3"
             x-data="{
                 text: @js($message->body),
                 copied: false,
                 track(channel) {
                     fetch('{{ route('share-events.store') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                         body: JSON.stringify({ content_type: 'message', content_id: {{ $message->id }}, channel }) });
                 },
                 copy() { navigator.clipboard.writeText(this.text); this.copied = true; this.track('copy_link'); setTimeout(() => this.copied = false, 2000); },
             }">
            <button type="button" @click="copy()" class="rounded-full bg-brand-600 px-5 py-2 font-semibold text-white hover:bg-brand-700">
                <span x-show="!copied">Copy message</span>
                <span x-show="copied" x-cloak>Copied!</span>
            </button>
            <a :href="`https://wa.me/?text=${encodeURIComponent(text)}`" target="_blank" rel="noopener" @click="track('whatsapp')"
               class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white hover:bg-emerald-800">Share on WhatsApp</a>
        </div>

        @if ($related->isNotEmpty())
            <h2 class="mt-12 text-lg font-bold">More {{ Str::lower($message->categoryLabel()) }}</h2>
            <div class="mt-3 space-y-3">
                @foreach ($related as $r)
                    <a href="{{ route('messages.show', $r) }}" class="block rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-700 hover:shadow">{{ Str::limit($r->body, 120) }}</a>
                @endforeach
            </div>
        @endif
    </div>
</x-layout>
