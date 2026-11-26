<x-layout :title="'“'.Str::limit($quote->body, 55).'” — '.$quote->author.' | BuntyFun'" :description="Str::limit($quote->shareText(), 150)" :noindex="true"
          :breadcrumbs="['Quotes' => route('quotes.index'), \App\Content\QuoteTopics::label($quote->topic) => route('quotes.topic', $quote->topic), 'Quote' => null]">
    <div class="mx-auto max-w-2xl px-4 py-12 text-center" x-data="{ copied: false, text: {{ Js::from($quote->shareText()) }} }">
        <div class="rounded-3xl bg-linear-to-br from-navy-600 to-brand-600 p-8 text-white shadow-lg sm:p-12">
            <blockquote class="text-2xl font-semibold leading-snug sm:text-3xl">“{{ $quote->body }}”</blockquote>
            <x-speak :text="$quote->body.' — '.$quote->author" lang="en" :full="true" :player="'quote:'.$quote->id" class="mt-4" />
            <p class="mt-5 text-lg opacity-90">— {{ $quote->author }}</p>
            <p class="mt-6 text-xs opacity-70">buntyfun.com/quotes</p>
        </div>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <button type="button" @click="BF.share(text)" class="rounded-full bg-brand-600 px-6 py-2 font-semibold text-white hover:bg-brand-700">Share</button>
            <a :href="'https://wa.me/?text=' + encodeURIComponent(text + ' ' + location.href)" target="_blank" rel="noopener" class="rounded-full bg-emerald-700 px-6 py-2 font-semibold text-white">WhatsApp</a>
            <button type="button" @click="BF.copy(text).then(ok => { copied = ok; setTimeout(() => copied = false, 1500) })" class="rounded-full border border-slate-300 px-6 py-2 font-semibold"><span x-show="!copied">Copy</span><span x-show="copied" x-cloak>Copied!</span></button>
        </div>
        <p class="mt-4 text-sm text-slate-500">Tip: take a screenshot of the card to post it as a status.</p>
    </div>
    @if ($more->isNotEmpty())
        <div class="mx-auto max-w-6xl px-4 pb-12">
            <h2 class="text-xl font-bold">More {{ strtolower(\App\Content\QuoteTopics::label($quote->topic)) }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($more as $quote) @include('quotes.partials.card') @endforeach
            </div>
        </div>
    @endif
</x-layout>
