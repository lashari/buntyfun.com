<x-layout title="Quran Online with Audio, Urdu & English Translation | BuntyFun"
          description="Read the Holy Quran online: all 114 surahs in Arabic (Uthmani script) with translation, verse-by-verse audio by Abdul Basit, Husary, Minshawi and other qualified reciters, repeat and bookmarks."
          :breadcrumbs="['Quran' => null]" :quranFont="true">
    <div class="mx-auto max-w-6xl px-4 py-10" x-data="quranIndex()" x-init="init()">
        <h1 class="text-3xl font-bold">The Holy Quran</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">All 114 surahs in the Arabic Uthmani script, with translation and verse-by-verse recitation. Choose a reciter on any surah page; your choice, bookmarks and last position are saved on this device only.</p>

        <form action="{{ route('quran.search') }}" method="GET" class="mt-6 flex max-w-xl gap-2" role="search">
            <label for="qs" class="sr-only">Search the Quran</label>
            <input id="qs" type="search" name="q" placeholder="Search a word in Arabic or the translation" class="w-full rounded-full border-slate-300 px-4 py-2">
            <button class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white">Search</button>
        </form>

        <template x-if="last">
            <a :href="last.url" class="mt-6 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4 hover:bg-emerald-100">
                <span><span class="block text-xs font-semibold uppercase tracking-wide text-emerald-700">Continue reading</span><span class="font-semibold" x-text="last.label"></span></span><span aria-hidden="true">→</span>
            </a>
        </template>
        <template x-if="bookmarks.length">
            <section class="mt-6">
                <h2 class="text-lg font-bold">Your bookmarks</h2>
                <ul class="mt-2 flex flex-wrap gap-2">
                    <template x-for="b in bookmarks" :key="b.key">
                        <li class="flex items-center gap-1 rounded-full border border-slate-200 bg-white pl-3 text-sm">
                            <a :href="b.url" class="py-1.5 font-medium hover:underline" x-text="b.label"></a>
                            <button type="button" @click="remove(b.key)" class="grid h-8 w-8 place-items-center rounded-full text-slate-400 hover:bg-slate-100" :aria-label="'Remove bookmark ' + b.label">✕</button>
                        </li>
                    </template>
                </ul>
            </section>
        </template>

        <label class="mt-8 block max-w-sm"><span class="sr-only">Filter surahs</span>
            <input type="search" x-model="f" placeholder="Find a surah (name or number)" class="w-full rounded-full border-slate-300 px-4 py-2">
        </label>
        <ol class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($surahs as $s)
                <li x-show="match({{ Js::from($s->number.' '.$s->name_en.' '.$s->name_meaning.' '.$s->name_ar) }})">
                    <a href="{{ $s->url() }}" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-emerald-50 text-sm font-bold text-emerald-800">{{ $s->number }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold">{{ $s->name_en }}</span>
                            <span class="block text-xs text-slate-500">{{ $s->name_meaning }} · {{ $s->ayah_count }} verses · {{ $s->revelation === 'meccan' ? 'Makki' : 'Madani' }}</span>
                        </span>
                        <span class="font-quran text-xl text-navy-800" dir="rtl" lang="ar">{{ $s->name_ar }}</span>
                    </a>
                </li>
            @endforeach
        </ol>
        @if ($reciters->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-lg font-bold">Reciters</h2>
                <p class="mt-1 text-sm text-slate-600">Verse-by-verse recordings by qualified qaris, streamed unaltered from public Quran audio libraries.</p>
                <ul class="mt-3 grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($reciters as $r)<li class="rounded-lg border border-slate-200 bg-white px-3 py-2"><span class="font-medium">{{ $r->name }}</span> <span class="text-slate-500">· {{ \App\Models\QuranReciter::STYLES[$r->style] ?? $r->style }}@if ($r->country) · {{ $r->country }}@endif</span></li>@endforeach
                </ul>
            </section>
        @endif
        <p class="mt-10 text-xs leading-relaxed text-slate-500">Arabic text: <a href="https://tanzil.net" class="underline" rel="noopener" target="_blank">Tanzil Project</a> (Uthmani), used verbatim under its licence. Translations are shown with their translator's name. Audio is never generated, edited or sped up. Report a mistake on our <a href="{{ route('pages.contact') }}" class="underline">contact page</a>.</p>
        <x-explore group="islamic" />
    </div>
    <script>
        function quranIndex() {
            return {
                f: '', last: null, bookmarks: [],
                init() { this.last = BF.store.get('quran_last'); this.bookmarks = BF.store.get('quran_bookmarks', []); },
                match(t) { const q = this.f.trim().toLowerCase(); return !q || t.toLowerCase().includes(q); },
                remove(k) { this.bookmarks = this.bookmarks.filter((b) => b.key !== k); BF.store.set('quran_bookmarks', this.bookmarks); },
            };
        }
    </script>
</x-layout>
