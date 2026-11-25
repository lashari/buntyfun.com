@props(['title', 'href' => null, 'more' => 'See all', 'sub' => null, 'id' => null])
<div class="mb-4 flex flex-wrap items-end justify-between gap-2">
    <div>
        <h2 @if ($id) id="{{ $id }}" @endif class="text-2xl font-bold"><span class="mr-2 inline-block h-6 w-1.5 rounded-full bg-brand-500 align-[-3px]" aria-hidden="true"></span>{{ $title }}</h2>
        @if ($sub)<p class="mt-1 text-sm text-slate-500">{{ $sub }}</p>@endif
    </div>
    @if ($href)<a href="{{ $href }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">{{ $more }} →</a>@endif
</div>
