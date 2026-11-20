<x-layout title="Birthday Wishes for Family, Friends & Colleagues — BuntyFun" description="Original birthday wishes for mom, dad, best friends, siblings, spouses and colleagues, in funny, emotional and short styles, with tips to personalise them."
          :noindex="request()->hasAny(['relationship', 'tone', 'language', 'page'])">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">Birthday Wishes</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">Original birthday messages you can copy or send on WhatsApp in a few seconds. Pick who the wish is for, then choose a tone: funny for group chats, emotional for cards and private messages, short for a quick text. Each relationship page also has tips for adding a personal line, which is what makes a copied wish feel like your own.</p>

        @if ($hubs)
            <div class="mt-6 flex flex-wrap gap-2">
                <a href="{{ route('birthday-messages.urdu') }}" class="rounded-full border border-orange-200 bg-orange-50 px-4 py-1.5 text-sm font-medium text-orange-700 hover:bg-orange-100">In Urdu · <span class="font-urdu">اردو میں</span></a>
                @foreach ($hubs as $r)
                    <a href="{{ route('birthday-messages.relationship', \App\Models\BirthdayMessage::relationshipSlug($r)) }}"
                       class="rounded-full border border-violet-200 bg-white px-4 py-1.5 text-sm font-medium text-violet-700 hover:bg-violet-50">For {{ \App\Content\BirthdayGuides::for($r)['label'] }}</a>
                @endforeach
            </div>
        @endif

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
                    <p class="mt-2 text-xs uppercase tracking-wide text-slate-500">{{ Str::headline($message->relationship) }} · {{ Str::headline($message->tone) }}</p>
                </a>
            @empty
                <p class="text-sm text-slate-500">No wishes match these filters yet.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $messages->links() }}</div>
    </div>
</x-layout>
