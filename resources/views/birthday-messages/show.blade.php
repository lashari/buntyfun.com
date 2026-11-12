<x-layout :dir="$message->language === 'ur' ? 'rtl' : 'ltr'" :lang="$message->language"
          :title="Str::headline($message->tone).' Birthday Wish for '.Str::headline($message->relationship).': '.Str::limit($message->body, 38).' — BuntyFun'" :description="Str::limit($message->body, 140).' Copy or share this birthday wish.'">
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

        <a href="{{ route('birthday-messages.index') }}" class="mt-8 block text-sm text-slate-500 hover:underline">More birthday wishes</a>
    </div>
</x-layout>
