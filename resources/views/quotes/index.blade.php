<x-layout title="Quotes — Motivational, Life, Love and Wisdom Quotes to Share | BuntyFun"
          :description="'Search '.number_format($total).' famous quotes by topic and author. A new quote of the day, plus one-tap copy and WhatsApp sharing.'"
          :noindex="$q !== '' || $total < 10" :breadcrumbs="['Quotes' => null]">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">Quotes</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">Famous quotes on motivation, life, success, friendship and more, sorted by topic and author. Copy one for a caption or send it straight to WhatsApp. A new quote of the day appears here every morning.</p>

        @if ($today && $q === '')
            <section class="mt-8 rounded-2xl bg-linear-to-br from-brand-50 to-white p-6 ring-1 ring-brand-100 sm:p-10">
                <p class="text-xs font-semibold uppercase tracking-wide text-navy-600">Quote of the day · {{ now()->format('M j, Y') }}</p>
                <blockquote class="mt-3 text-2xl font-semibold leading-snug text-slate-900 sm:text-3xl">“{{ $today->body }}”</blockquote>
                <p class="mt-3 text-slate-600">— {{ $today->author }}</p>
                <div class="mt-5 flex flex-wrap gap-2" x-data="{ copied: false, text: {{ Js::from($today->shareText()) }} }">
                    <button type="button" @click="BF.share(text, {{ Js::from(route('quotes.show', $today)) }})" class="rounded-full bg-brand-600 px-5 py-2 font-semibold text-white hover:bg-brand-700">Share</button>
                    <button type="button" @click="BF.copy(text).then(ok => { copied = ok; setTimeout(() => copied = false, 1500) })" class="rounded-full border border-slate-300 bg-white px-5 py-2 font-semibold hover:bg-slate-50"><span x-show="!copied">Copy</span><span x-show="copied" x-cloak>Copied!</span></button>
                </div>
            </section>
        @endif

        <form method="GET" action="{{ route('quotes.index') }}" class="mt-8 flex max-w-xl gap-2" role="search">
            <label for="quote-q" class="sr-only">Search quotes</label>
            <input id="quote-q" type="search" name="q" value="{{ $q }}" placeholder="Search a word or an author…" class="w-full rounded-full border border-slate-300 px-4 py-2">
            <button class="rounded-full bg-navy-600 px-5 py-2 font-semibold text-white">Search</button>
        </form>

        @if ($results)
            <h2 class="mt-8 text-xl font-bold">{{ $results->total() }} {{ Str::plural('result', $results->total()) }} for “{{ $q }}”</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($results as $quote) @include('quotes.partials.card') @endforeach
            </div>
            <div class="mt-6">{{ $results->links() }}</div>
        @endif

        <h2 class="mt-10 text-xl font-bold">Browse by topic</h2>
        @include('quotes.partials.topics', ['current' => null])

        @if ($authors->isNotEmpty())
            <h2 class="mt-10 text-xl font-bold">Popular authors</h2>
            <ul class="mt-4 flex flex-wrap gap-2 text-sm">
                @foreach ($authors as $a)
                    <li><a href="{{ route('quotes.author', $a->author_slug) }}" class="block rounded-lg bg-slate-100 px-3 py-1.5 hover:bg-brand-50">{{ $a->author }} <span class="text-slate-500">{{ $a->c }}</span></a></li>
                @endforeach
            </ul>
        @endif

        <section class="prose-page mt-12 max-w-3xl">
            <h2>About these quotes</h2>
            <p>The collection is based on QuoteSlate, an open (MIT-licensed) quote list, and every quote is read by our editors before it is published. Famous lines are often credited to the wrong person; when we cannot confirm an attribution we leave the quote out or mark the author as Anonymous.</p>
            <p>Looking for wishes instead? See <a href="{{ route('birthday-messages.index') }}">birthday wishes</a> and <a href="{{ route('messages.index') }}">messages for every occasion</a>.</p>
            <p class="text-sm text-slate-500">Source collection: <a href="https://github.com/musheer360/QuoteSlate" rel="noopener nofollow" target="_blank">QuoteSlate</a> by Musheer Alam (MIT licence).</p>
        </section>
    </div>
</x-layout>
