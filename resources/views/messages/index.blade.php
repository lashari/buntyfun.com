@php
    $label = $category ? (\App\Models\Message::CATEGORIES[$category][0] ?? 'Messages') : 'Messages & Quotes';
@endphp
<x-layout :title="$label.' — BuntyFun'" :description="$category ? 'Original '.Str::lower($label).' you can copy or share on WhatsApp.' : 'Original good morning, Eid, congratulations, friendship and motivational messages to copy and share.'"
          :breadcrumbs="$category ? ['Messages' => route('messages.index'), $label => null] : ['Messages' => null]"
          :ads="! $category || (\App\Models\Message::CATEGORIES[$category][1] ?? true)"
          :noindex="! \App\Models\Message::indexable()->exists() || request()->has('page')">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ $label }}</h1>

        @if ($categories->isNotEmpty())
            <div class="mt-4 flex flex-wrap gap-2 text-sm">
                <a href="{{ route('messages.index') }}" class="rounded-full border px-3 py-1 {{ $category ? 'border-slate-300 text-slate-600' : 'border-brand-600 bg-brand-600 text-white' }}">All</a>
                @foreach ($categories as $key => $name)
                    <a href="{{ route('messages.index', ['category' => $key]) }}" class="rounded-full border px-3 py-1 {{ $category === $key ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-300 text-slate-600 hover:border-brand-400' }}">{{ $name }}</a>
                @endforeach
            </div>
        @endif

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @forelse ($messages as $message)
                <a href="{{ route('messages.show', $message) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                    @if ($message->title)<p class="font-semibold">{{ $message->title }}</p>@endif
                    <p class="mt-1 text-sm text-slate-700" @if ($message->language === 'ur') dir="rtl" @endif>{{ Str::limit($message->body, 140) }}</p>
                    <p class="mt-2 text-xs uppercase tracking-wide text-slate-500">{{ $message->categoryLabel() }}</p>
                </a>
            @empty
                <p class="text-sm text-slate-500">No messages here yet.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $messages->links() }}</div>
    </div>
</x-layout>
