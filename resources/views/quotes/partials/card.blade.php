<figure class="flex flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
        x-data="{ copied: false, text: {{ Js::from($quote->shareText()) }} }">
    <blockquote class="text-lg leading-relaxed text-slate-800">“{{ $quote->body }}”</blockquote>
    <figcaption class="mt-3 text-sm font-medium text-slate-600">
        — @if ($quote->author !== 'Anonymous')<a href="{{ route('quotes.author', $quote->author_slug) }}" class="hover:underline">{{ $quote->author }}</a>@else Anonymous @endif
    </figcaption>
    <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
        <button type="button" @click="BF.copy(text + ' — buntyfun.com').then(ok => { copied = ok; setTimeout(() => copied = false, 1500) })" class="rounded-full border border-slate-300 px-3 py-1.5 hover:bg-slate-50">
            <span x-show="!copied">Copy</span><span x-show="copied" x-cloak>Copied!</span>
        </button>
        <a :href="'https://wa.me/?text=' + encodeURIComponent(text + ' {{ route('quotes.show', $quote) }}')" target="_blank" rel="noopener" class="rounded-full bg-emerald-700 px-3 py-1.5 text-white hover:bg-emerald-800">WhatsApp</a>
        <a href="{{ route('quotes.show', $quote) }}" class="rounded-full border border-slate-300 px-3 py-1.5 hover:bg-slate-50">Share card</a>
    </div>
</figure>
