@if (! empty($topics))
    <nav aria-label="Quote topics" class="mt-6 flex flex-wrap gap-2">
        @foreach ($topics as $key => $count)
            <a href="{{ route('quotes.topic', $key) }}" class="rounded-full border px-3 py-1.5 text-sm font-medium {{ ($current ?? null) === $key ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 bg-white hover:border-brand-300' }}">
                {{ str_replace(' Quotes', '', \App\Content\QuoteTopics::label($key)) }} <span class="opacity-70">{{ $count }}</span>
            </a>
        @endforeach
    </nav>
@endif
