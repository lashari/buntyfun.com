<x-layout title="Birthday Wishes — BuntyFun" description="Birthday wishes and messages for every relationship and tone.">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">Birthday Wishes</h1>

        <form method="GET" class="mt-4 flex flex-wrap gap-3">
            <select name="relationship" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">All relationships</option>
                @foreach ($relationships as $relationship)
                    <option value="{{ $relationship }}" @selected(request('relationship') === $relationship)>{{ Str::headline($relationship) }}</option>
                @endforeach
            </select>
            <select name="tone" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">All tones</option>
                @foreach ($tones as $tone)
                    <option value="{{ $tone }}" @selected(request('tone') === $tone)>{{ Str::headline($tone) }}</option>
                @endforeach
            </select>
            <select name="language" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">English &amp; Urdu</option>
                <option value="en" @selected(request('language') === 'en')>English</option>
                <option value="ur" @selected(request('language') === 'ur')>اردو</option>
            </select>
        </form>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @forelse ($messages as $message)
                <a href="{{ route('birthday-messages.show', $message) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md">
                    <p class="text-sm text-slate-700" @if ($message->language === 'ur') dir="rtl" @endif>{{ Str::limit($message->body, 140) }}</p>
                    <p class="mt-2 text-xs uppercase tracking-wide text-slate-400">{{ Str::headline($message->relationship) }} · {{ Str::headline($message->tone) }}</p>
                </a>
            @empty
                <p class="text-sm text-slate-500">No wishes match these filters yet.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $messages->links() }}</div>
    </div>
</x-layout>
