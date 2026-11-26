@php
    $title = 'Surah '.$surah->name_en.' ('.$surah->name_ar.') — Arabic Text, Translation & Audio';
    $hasBasmala = ! in_array($surah->number, [1, 9], true);
    $config = [
        'surah' => $surah->number, 'name' => $surah->name_en, 'basmala' => $hasBasmala,
        'ayahs' => $ayahs->map(fn ($a) => ['g' => $a->id, 'a' => $a->ayah])->values(),
        'reciters' => $reciters->map(fn ($r) => ['slug' => $r->slug, 'name' => $r->name, 'pattern' => $r->url_pattern])->values(),
        'url' => $surah->url(),
    ];
@endphp
<x-layout :title="$title.' | BuntyFun'"
          :description="'Read Surah '.$surah->name_en.' ('.$surah->name_meaning.'), '.$surah->ayah_count.' verses, in Arabic with translation and verse-by-verse audio recitation. Repeat, bookmark and continue reading.'"
          :breadcrumbs="['Quran' => route('quran.index'), 'Surah '.$surah->name_en => null]" :quranFont="true" :urduFont="true" :player="true">
    <div class="mx-auto max-w-4xl px-4 py-8" x-data="quranSurah({{ Js::from($config) }})" x-init="init()" @bf-player.window="onPlayer($event.detail)">
        <header class="text-center">
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Surah {{ $surah->number }} · {{ $surah->revelation === 'meccan' ? 'Makki' : 'Madani' }} · {{ $surah->ayah_count }} verses</p>
            <h1 class="mt-1 text-3xl font-bold">Surah {{ $surah->name_en }} <span class="font-quran block text-4xl font-normal text-navy-800" dir="rtl" lang="ar">{{ $surah->name_ar }}</span></h1>
            <p class="text-slate-600">{{ $surah->name_meaning }}</p>
        </header>

        {{-- controls --}}
        <div class="sticky top-0 z-30 mt-6 rounded-xl border border-slate-200 bg-white/95 p-3 shadow-sm backdrop-blur">
            <div class="flex flex-wrap items-end gap-3 text-sm">
                @if ($reciters->isNotEmpty())
                    <label class="min-w-[12rem] flex-1">
                        <span class="block text-xs font-semibold text-slate-500">Reciter</span>
                        <select x-model="reciter" @change="saveReciter()" class="mt-1 w-full rounded-lg border-slate-300 py-1.5">
                            @foreach ($reciters as $r)<option value="{{ $r->slug }}">{{ $r->name }} ({{ \App\Models\QuranReciter::STYLES[$r->style] ?? $r->style }})</option>@endforeach
                        </select>
                    </label>
                    <label>
                        <span class="block text-xs font-semibold text-slate-500">From verse</span>
                        <input type="number" min="1" :max="cfg.ayahs.length" x-model.number="from" class="mt-1 w-20 rounded-lg border-slate-300 py-1.5">
                    </label>
                    <label>
                        <span class="block text-xs font-semibold text-slate-500">To verse</span>
                        <input type="number" min="1" :max="cfg.ayahs.length" x-model.number="to" class="mt-1 w-20 rounded-lg border-slate-300 py-1.5">
                    </label>
                    <label class="flex items-center gap-2 pb-2"><input type="checkbox" x-model="loop" class="rounded"> Loop range</label>
                    <button type="button" @click="playRange()" class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white hover:bg-emerald-800">▶ Play</button>
                @else
                    <p class="text-slate-500">Audio recitation is being set up.</p>
                @endif
            </div>
            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
                @foreach ($translations as $t)
                    <label class="flex items-center gap-1.5"><input type="checkbox" x-model="show.{{ $t->language === 'ur' ? 'ur' : 'tr'.$t->id }}" class="rounded"> {{ $t->name }}</label>
                @endforeach
                <label class="flex items-center gap-1.5"><input type="checkbox" x-model="follow" class="rounded"> Follow the recitation</label>
                <span>Use the player's Repeat menu to repeat each verse.</span>
            </div>
        </div>

        <style>.hide-ur .tr-ur{display:none}@foreach ($translations as $t) .hide-{{ $t->id }} .tr-{{ $t->id }}{display:none}@endforeach</style>
        @if ($hasBasmala)
            <p class="font-quran mt-8 text-center text-3xl text-navy-800" dir="rtl" lang="ar">بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ</p>
        @endif

        <ol class="mt-6 divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white" x-ref="list" @click="onClick($event)" :class="{ 'hide-ur': !show.ur @foreach ($translations as $t)@if ($t->language !== 'ur'), 'hide-{{ $t->id }}': !show.tr{{ $t->id }}@endif @endforeach }">
            @foreach ($ayahs as $a)
                @php
                    $arabic = $a->text_uthmani;
                    if ($hasBasmala && $a->ayah === 1) { $arabic = preg_replace('/^بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ\s+/u', '', $arabic); }
                @endphp
                <li id="ayah-{{ $a->ayah }}" data-g="{{ $a->id }}" class="scroll-mt-40 p-4 sm:p-5">
                    <div class="flex items-start gap-3">
                        <div class="flex shrink-0 flex-col items-center gap-1">
                            <span class="grid h-9 w-9 place-items-center rounded-full bg-emerald-50 text-xs font-bold text-emerald-800">{{ $a->ayah }}</span>
                            @if ($reciters->isNotEmpty())<button type="button" data-play="{{ $a->ayah }}" class="ab text-emerald-700" aria-label="Play verse {{ $a->ayah }}">▶</button>@endif
                            <button type="button" data-bm="{{ $a->ayah }}" class="ab bm" aria-pressed="false" aria-label="Bookmark verse {{ $a->ayah }}">★</button>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-quran text-right text-[1.7rem] text-navy-900 sm:text-3xl" dir="rtl" lang="ar">{{ $arabic }} <span class="text-xl text-emerald-700">﴿{{ strtr((string) $a->ayah, ['0'=>'٠','1'=>'١','2'=>'٢','3'=>'٣','4'=>'٤','5'=>'٥','6'=>'٦','7'=>'٧','8'=>'٨','9'=>'٩']) }}﴾</span></p>
                            @foreach ($translations as $t)
                                @php $tx = optional($texts[$a->id] ?? collect())->firstWhere('translation_id', $t->id); @endphp
                                @if ($tx)
                                    @if ($t->language === 'ur')
                                        <p class="tr-ur font-urdu mt-3 text-right text-lg leading-loose text-slate-700" dir="rtl" lang="ur">{{ $tx->text }}</p>
                                    @else
                                        <p class="tr-{{ $t->id }} mt-3 leading-relaxed text-slate-700" lang="{{ $t->language }}">{{ $tx->text }}</p>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </div>
                </li>
            @endforeach
        </ol>

        <nav class="mt-8 flex justify-between gap-3 text-sm font-semibold" aria-label="Surahs">
            @if ($prev)<a href="{{ $prev->url() }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 hover:bg-slate-50">← {{ $prev->number }}. {{ $prev->name_en }}</a>@else<span></span>@endif
            <a href="{{ route('quran.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 hover:bg-slate-50">All surahs</a>
            @if ($next)<a href="{{ $next->url() }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 hover:bg-slate-50">{{ $next->number }}. {{ $next->name_en }} →</a>@else<span></span>@endif
        </nav>
        <p class="mt-8 text-xs leading-relaxed text-slate-500">
            Arabic text: <a href="https://tanzil.net" class="underline" rel="noopener" target="_blank">Tanzil Project</a> Uthmani text, used verbatim (copyright notice and updates at tanzil.net).
            @foreach ($translations as $t) Translation: {{ $t->translator }} — {{ $t->license }}. @endforeach
            Recitations are streamed unaltered; speed controls are off for Quran audio.
        </p>
    </div>
    <script>
        function quranSurah(cfg) {
            return {
                cfg, reciter: null, from: 1, to: cfg.ayahs.length, loop: false, follow: true, active: null,
                show: Object.assign({ ur: true }, BF.store.get('quran_show', {})), bookmarks: [],
                init() {
                    const r = BF.reciter(cfg.reciters); this.reciter = r ? r.slug : null;
                    this.bookmarks = BF.store.get('quran_bookmarks', []);
                    this.paintBookmarks();
                    @foreach ($translations as $t) if (this.show['tr{{ $t->id }}'] === undefined) this.show['tr{{ $t->id }}'] = true; @endforeach
                    this.$watch('show', (v) => BF.store.set('quran_show', v), { deep: true });
                    const m = location.hash.match(/^#ayah-(\d+)$/); if (m) { this.from = Number(m[1]); }
                },
                saveReciter() { BF.store.set('quran_reciter', this.reciter); },
                rec() { return cfg.reciters.find((r) => r.slug === this.reciter) || cfg.reciters[0]; },
                track(x, label) {
                    const r = this.rec();
                    return { src: BF.quranSrc(r.pattern, x.g, cfg.surah, x.a), title: 'Surah ' + cfg.name + ' — ' + (label || ('verse ' + x.a)), subtitle: r.name, key: x.g, album: 'Quran' };
                },
                queue(a1, a2) {
                    const q = [];
                    if (cfg.basmala && a1 === 1) { const r = this.rec(); q.push({ src: BF.quranSrc(r.pattern, 1, 1, 1), title: 'Surah ' + cfg.name + ' — Bismillah', subtitle: r.name, key: 'basmala' }); }
                    cfg.ayahs.filter((x) => x.a >= a1 && x.a <= a2).forEach((x) => q.push(this.track(x)));
                    return q;
                },
                playFrom(a) { this.from = a; this.playRange(true); },
                playRange(toEnd = false) {
                    if (!this.rec()) return;
                    const n = cfg.ayahs.length;
                    const a1 = Math.min(Math.max(1, this.from || 1), n);
                    const a2 = toEnd ? n : Math.min(Math.max(a1, this.to || n), n);
                    BFPlayer.load(this.queue(a1, a2), 0, { allowSpeed: false, loopAll: this.loop });
                    BF.track('quran_play', { surah: cfg.surah, reciter: this.reciter });
                    this.saveLast(a1);
                },
                onClick(e) {
                    const p = e.target.closest('[data-play]'); if (p) { this.playFrom(Number(p.dataset.play)); return; }
                    const b = e.target.closest('[data-bm]'); if (b) this.toggleBookmark(Number(b.dataset.bm));
                },
                paintBookmarks() {
                    this.$refs.list.querySelectorAll('[data-bm]').forEach((el) => {
                        const on = this.isBookmarked(Number(el.dataset.bm));
                        el.setAttribute('aria-pressed', on ? 'true' : 'false');
                        el.classList.toggle('on', on);
                    });
                },
                onPlayer(d) {
                    const x = cfg.ayahs.find((y) => y.g === d.key);
                    this.$refs.list.querySelector('.ayah-active')?.classList.remove('ayah-active');
                    if (x) this.$refs.list.querySelector('[data-g="' + x.g + '"]')?.classList.add('ayah-active');
                    this.active = x ? x.g : null;
                    if (x) { this.saveLast(x.a); if (this.follow && d.playing) document.getElementById('ayah-' + x.a)?.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
                },
                saveLast(a) { BF.store.set('quran_last', { url: cfg.url + '#ayah-' + a, label: 'Surah ' + cfg.name + ', verse ' + a }); },
                key(a) { return cfg.surah + ':' + a; },
                isBookmarked(a) { return this.bookmarks.some((b) => b.key === this.key(a)); },
                toggleBookmark(a) {
                    const k = this.key(a);
                    this.bookmarks = this.isBookmarked(a) ? this.bookmarks.filter((b) => b.key !== k)
                        : [{ key: k, url: cfg.url + '#ayah-' + a, label: cfg.name + ' ' + k }, ...this.bookmarks].slice(0, 50);
                    BF.store.set('quran_bookmarks', this.bookmarks);
                    this.paintBookmarks();
                },
            };
        }
    </script>
</x-layout>
