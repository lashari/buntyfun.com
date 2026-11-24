<x-layout :title="$heading.($quotes->currentPage() > 1 ? ' — Page '.$quotes->currentPage() : '').' — '.$quotes->total().' Quotes to Copy and Share | BuntyFun'"
          :description="Str::limit($intro, 150)" :noindex="$noindex"
          :breadcrumbs="['Quotes' => route('quotes.index'), $heading => null]">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ $heading }}</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">{{ $intro }}</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($quotes as $quote) @include('quotes.partials.card') @endforeach
        </div>
        <div class="mt-8">{{ $quotes->links() }}</div>
        <h2 class="mt-12 text-xl font-bold">More quote topics</h2>
        @include('quotes.partials.topics')
        <p class="mt-8 text-sm text-slate-500"><a href="{{ route('daily') }}" class="underline">Quote of the day</a> · <a href="{{ route('quotes.index') }}" class="underline">Search all quotes</a></p>
    </div>
</x-layout>
