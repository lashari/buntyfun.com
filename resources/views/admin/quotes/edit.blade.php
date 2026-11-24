<x-admin-layout title="Edit quote">
    <a href="{{ route('admin.quotes.index') }}" class="text-sm text-navy-600 underline">← Quotes</a>
    <h1 class="mt-2 text-2xl font-bold">Edit quote #{{ $quote->id }}</h1>
    @foreach ($quote->flags ?? [] as $f)<p class="mt-2 rounded bg-amber-50 px-3 py-1.5 text-sm text-amber-800">⚠ {{ $f }}</p>@endforeach
    <form method="POST" action="{{ route('admin.quotes.update', $quote) }}" class="mt-4 max-w-2xl space-y-3 rounded-xl border border-slate-200 bg-white p-5 text-sm">
        @csrf @method('PUT')
        @if ($errors->any())<ul class="rounded bg-red-50 px-6 py-2 text-red-700">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>@endif
        <label class="block font-semibold">Quote<textarea name="body" rows="3" class="mt-1 w-full rounded border-slate-300">{{ old('body', $quote->body) }}</textarea></label>
        <label class="block font-semibold">Author <span class="font-normal text-slate-500">(use "Anonymous" if the source cannot be confirmed)</span><input name="author" value="{{ old('author', $quote->author) }}" class="mt-1 w-full rounded border-slate-300"></label>
        <div class="flex flex-wrap gap-4">
            <label>Topic <select name="topic" class="rounded border-slate-300">@foreach (\App\Content\QuoteTopics::ALL as $k => $t)<option value="{{ $k }}" @selected(old('topic', $quote->topic) === $k)>{{ $t[0] }}</option>@endforeach</select></label>
            <label>Status <select name="status" class="rounded border-slate-300">@foreach (\App\Models\Quote::STATUSES as $k => $l)<option value="{{ $k }}" @selected(old('status', $quote->status) === $k)>{{ $l }}</option>@endforeach</select></label>
            <label>Or schedule publish at <input type="datetime-local" name="meta_publish_at" value="{{ old('meta_publish_at', $quote->meta?->publish_at?->format('Y-m-d\TH:i')) }}" class="rounded border-slate-300"></label>
        </div>
        <p class="text-xs text-slate-500">To schedule: leave status In review and pick a time; the 5-minute automation publishes it then.</p>
        <button class="rounded-lg bg-brand-600 px-4 py-2 font-semibold text-white">Save</button>
    </form>
</x-admin-layout>
