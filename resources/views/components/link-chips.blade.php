{{-- A row of plain text links (descriptive anchors), used for "explore" and related-content blocks. --}}
@props(['links' => [], 'label' => null])
@if (count($links))
    <ul {{ $attributes->merge(['class' => 'flex flex-wrap gap-2 text-sm']) }} @if ($label) aria-label="{{ $label }}" @endif>
        @foreach ($links as [$href, $text])
            <li><a href="{{ $href }}" class="block rounded-full border border-slate-300 bg-white px-3 py-1.5 text-slate-700 hover:border-brand-400 hover:text-brand-700">{{ $text }}</a></li>
        @endforeach
    </ul>
@endif
