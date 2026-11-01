<x-layout dir="rtl" lang="ur" :title="Str::limit($item->body, 50).' — BuntyFun'" description="اردو میں پیغام شیئر کریں۔">
    <div class="mx-auto max-w-xl px-4 py-12">
        <p class="text-xs uppercase tracking-wide text-slate-400">{{ $item->category }}</p>
        <p class="mt-4 text-xl leading-relaxed">{{ $item->body }}</p>

        <div class="mt-8 flex flex-wrap gap-3"
             x-data="{
                 text: @js($item->body),
                 copied: false,
                 copy() {
                     navigator.clipboard.writeText(this.text);
                     this.copied = true;
                     fetch('{{ route('share-events.store') }}', {
                         method: 'POST',
                         headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                         body: JSON.stringify({ content_type: 'urdu_content', content_id: {{ $item->id }}, channel: 'copy_link' }),
                     });
                     setTimeout(() => this.copied = false, 2000);
                 },
             }">
            <button type="button" @click="copy()" class="rounded-full bg-violet-600 px-5 py-2 font-semibold text-white hover:bg-violet-700">
                <span x-show="!copied">کاپی کریں</span>
                <span x-show="copied" x-cloak>کاپی ہو گیا!</span>
            </button>
            <a :href="`https://wa.me/?text=${encodeURIComponent(text)}`" target="_blank" rel="noopener"
               @click="fetch('{{ route('share-events.store') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ content_type: 'urdu_content', content_id: {{ $item->id }}, channel: 'whatsapp' }) })"
               class="rounded-full bg-emerald-600 px-5 py-2 font-semibold text-white hover:bg-emerald-700">
                واٹس ایپ پر شیئر کریں
            </a>
        </div>

        <a href="{{ route('urdu-content.index') }}" class="mt-8 block text-sm text-slate-500 hover:underline">مزید اردو مواد</a>
    </div>
</x-layout>
