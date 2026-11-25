<x-layout title="Daily Duas in Arabic with English & Urdu Translation | BuntyFun"
          description="Short daily duas and adhkar in Arabic with transliteration, English and Urdu meaning and the hadith or Quran reference: eating, sleeping, travel, forgiveness, parents and more."
          :breadcrumbs="['Duas' => null]" :noindex="$groups->isEmpty()" :urduFont="true">
    <div class="mx-auto max-w-5xl px-4 py-10">
        <h1 class="text-3xl font-bold">Daily Duas &amp; Adhkar</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">Short supplications for everyday moments, each with its Arabic text, a transliteration, the meaning in English and Urdu, and the Quran verse or hadith it comes from, so you can check it yourself.</p>
        @forelse ($groups as $cat => $duas)
            <section class="mt-8">
                <h2 class="text-xl font-bold">{{ \App\Models\Dua::CATEGORIES[$cat] ?? Str::headline($cat) }}</h2>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach ($duas as $d)
                        <a href="{{ route('duas.show', $d) }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md">
                            <span class="font-semibold">{{ $d->title }}</span>
                            <span class="mt-2 block text-right text-xl leading-loose text-navy-700" dir="rtl" lang="ar">{{ Str::limit($d->arabic, 90) }}</span>
                            <span class="mt-1 block text-xs text-slate-500">{{ $d->reference }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @empty
            <p class="mt-8 text-slate-500">Duas are being checked by our editors and will be published soon.</p>
        @endforelse
        <p class="mt-10 text-sm text-slate-500">See also the <a href="{{ route('islamic.allah-names') }}" class="underline">99 Names of Allah</a> and the <a href="{{ route('islamic.calendar') }}" class="underline">Islamic calendar</a>.</p>
        <x-explore group="islamic" />
    </div>
</x-layout>
