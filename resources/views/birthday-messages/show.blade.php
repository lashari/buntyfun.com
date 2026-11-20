@php $hub = in_array($message->relationship, \App\Models\BirthdayMessage::hubRelationships(), true) ? route('birthday-messages.relationship', \App\Models\BirthdayMessage::relationshipSlug($message->relationship)) : route('birthday-messages.index', ['relationship' => $message->relationship]); @endphp
<x-layout :dir="$message->language === 'ur' ? 'rtl' : 'ltr'" :lang="$message->language"
          :title="Str::headline($message->tone).' Birthday Wish for '.Str::headline($message->relationship).': '.Str::limit($message->body, 38).' — BuntyFun'" :description="Str::limit($message->body, 140).' Copy or share this birthday wish.'"
          :meta="$message->meta" :noindex="! config('site.index_single_snippets')" :breadcrumbs="['Birthday Wishes' => route('birthday-messages.index'), Str::headline($message->relationship) => $hub, 'Wish' => null]">
    <div class="mx-auto max-w-xl px-4 py-12">
        <p class="text-xs uppercase tracking-wide text-slate-500">{{ Str::headline($message->relationship) }} · {{ Str::headline($message->tone) }}</p>
        <p class="mt-4 text-xl leading-relaxed">{{ $message->body }}</p>

        <div class="mt-8 flex flex-wrap gap-3"
             x-data="{
                 text: @js($message->body),
                 copied: false,
                 copy() {
                     navigator.clipboard.writeText(this.text);
                     this.copied = true;
                     fetch('{{ route('share-events.store') }}', {
                         method: 'POST',
                         headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                         body: JSON.stringify({ content_type: 'birthday_message', content_id: {{ $message->id }}, channel: 'copy_link' }),
                     });
                     setTimeout(() => this.copied = false, 2000);
                 },
             }">
            <button type="button" @click="copy()" class="rounded-full bg-violet-600 px-5 py-2 font-semibold text-white hover:bg-violet-700">
                <span x-show="!copied">Copy message</span>
                <span x-show="copied" x-cloak>Copied!</span>
            </button>
            <a :href="`https://wa.me/?text=${encodeURIComponent(text)}`" target="_blank" rel="noopener"
               @click="fetch('{{ route('share-events.store') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ content_type: 'birthday_message', content_id: {{ $message->id }}, channel: 'whatsapp' }) })"
               class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white hover:bg-emerald-800">
                Share on WhatsApp
            </a>
        </div>

        @php
            $related = \App\Models\BirthdayMessage::where('status', 'published')->where('relationship', $message->relationship)
                ->whereKeyNot($message->id)->inRandomOrder()->limit(4)->get();
        @endphp
        @if ($related->isNotEmpty())
            <h2 class="mt-12 text-lg font-bold">More wishes for {{ Str::lower(Str::headline($message->relationship)) }}</h2>
            <div class="mt-3 space-y-3">
                @foreach ($related as $r)
                    <a href="{{ route('birthday-messages.show', $r) }}" class="block rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-700 hover:shadow" @if ($r->language === 'ur') dir="rtl" @endif>{{ Str::limit($r->body, 120) }}</a>
                @endforeach
            </div>
        @endif

        <a href="{{ $hub }}" class="mt-8 block text-sm font-medium text-violet-700 hover:underline">All birthday wishes for {{ Str::lower(Str::headline($message->relationship)) }}, with tips</a>
    </div>
</x-layout>
