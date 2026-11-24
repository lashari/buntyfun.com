<x-layout :title="'Urdu Corner (اردو) — Quotes, Jokes, Friendship & Family Messages | BuntyFun'"
          description="Original Urdu quotes, clean jokes, friendship, love and family messages written in Urdu. Copy or send on WhatsApp."
          :urdu-font="true" :noindex="request()->has('page') || request()->has('category')"
          :breadcrumbs="['اردو' => null]">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="font-urdu text-4xl font-bold leading-loose" dir="rtl" lang="ur">اردو تفریح</h1>
        <p class="mt-1 text-lg font-semibold text-slate-700">Urdu Corner</p>
        <p class="mt-4 max-w-3xl leading-relaxed text-slate-700">Quotes, clean jokes and messages for friends and family, written directly in Urdu rather than machine-translated. Pick a topic below, or scroll for the latest lines. Every line has a Copy and WhatsApp button on its page. We don't publish poetry, because most famous Urdu verse belongs to known poets and is often misquoted online.</p>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($pages as $url => $p)
                <a href="{{ route('urdu-content.page', $url) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="font-urdu text-2xl font-bold leading-loose" dir="rtl" lang="ur">{{ $p['h1'] }}</p>
                    <p class="text-sm font-semibold text-slate-700">{{ $p['subtitle'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $p['count'] }} lines</p>
                </a>
            @endforeach
            <a href="{{ route('birthday-messages.urdu') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <p class="font-urdu text-2xl font-bold leading-loose" dir="rtl" lang="ur">سالگرہ مبارک</p>
                <p class="text-sm font-semibold text-slate-700">Urdu Birthday Wishes</p>
            </a>
        </div>

        <h2 class="mt-12 text-xl font-bold">Latest <span class="font-urdu" lang="ur">· تازہ ترین</span></h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2" dir="rtl">
            @forelse ($content as $item)
                <a href="{{ route('urdu-content.show', $item) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md" lang="ur">
                    <p class="font-urdu text-lg leading-loose">{{ $item->body }}</p>
                    <p class="mt-2 font-urdu text-sm text-slate-500">{{ \App\Content\UrduPages::label($item->category) }}</p>
                </a>
            @empty
                <p class="font-urdu text-sm text-slate-500">ابھی کوئی مواد دستیاب نہیں ہے۔</p>
            @endforelse
        </div>
        <div class="mt-8" dir="ltr">{{ $content->links() }}</div>
    </div>
</x-layout>
