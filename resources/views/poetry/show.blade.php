@php
    $formName = \App\Models\Poem::FORMS[$poem->form][0] ?? 'Poem';
    $single = Str::singular($formName);
    $first = $poem->firstLines(1);
    $plain = trim(implode("\n\n", array_map(fn ($s) => implode("\n", $s), $poem->stanzas())));
    $schema = [['@context' => 'https://schema.org', '@type' => 'CreativeWork', 'name' => $poem->title, 'inLanguage' => 'ur', 'genre' => $single,
        'author' => ['@type' => 'Person', 'name' => $poem->poet->name_en, 'alternateName' => $poem->poet->name_ur], 'license' => 'https://creativecommons.org/licenses/by-sa/4.0/',
        'isBasedOn' => $poem->source_url, 'copyrightNotice' => 'Public domain text']];
@endphp
<x-layout :title="$poem->title.' — '.$poem->poet->name_en.' '.$single.' in Urdu | BuntyFun'"
          :description="$poem->poet->name_en.' '.Str::lower($single).': '.Str::limit($poem->firstLines(2), 120)"
          :meta="$poem->meta" :breadcrumbs="['Urdu Poetry' => route('poetry.index'), $poem->poet->name_en => route('poetry.segment', $poem->poet->slug), Str::limit($poem->title, 40) => null]"
          :schema="$schema" :urduFont="true" :noindex="$preview">
    <div class="mx-auto max-w-3xl px-4 py-10" x-data="poemCard({{ Js::from($plain) }}, {{ Js::from($poem->poet->name_ur) }})">
        @if ($preview)
            <p class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm text-amber-800">Preview: this poem is <strong>{{ Str::headline($poem->status) }}</strong>. Publish it from Admin → Library.</p>
        @endif
        <p class="text-sm font-semibold text-navy-600">{{ $single }} · <a href="{{ route('poetry.segment', $poem->poet->slug) }}" class="underline">{{ $poem->poet->name_en }}</a>@if ($poem->book) · <span class="font-urdu" lang="ur">{{ $poem->book }}</span>@endif</p>
        <h1 class="font-urdu mt-2 text-right text-3xl leading-[2.2]" dir="rtl" lang="ur">{{ $poem->title }}</h1>
        <article class="mt-6 rounded-2xl border border-slate-200 bg-white px-4 py-6 shadow-sm sm:px-8" dir="rtl" lang="ur">
            @foreach ($poem->stanzas() as $i => $st)
                <div class="font-urdu group relative py-3 text-center text-xl leading-[2.4] sm:text-2xl {{ $i ? 'border-t border-slate-100' : '' }}">
                    @foreach ($st as $line)<p>{{ $line }}</p>@endforeach
                    <button type="button" @click="pick({{ Js::from(implode("\n", $st)) }})" class="mt-1 text-xs text-navy-600 opacity-70 hover:opacity-100" dir="ltr">Share this couplet</button>
                </div>
            @endforeach
            <p class="font-urdu mt-4 text-left text-lg text-slate-600">— {{ $poem->poet->name_ur }}</p>
        </article>
        <div class="mt-4 flex flex-wrap gap-2">
            <button type="button" @click="copy(full)" class="rounded-full bg-slate-200 px-4 py-2 text-sm font-semibold"><span x-text="copied ? 'Copied ✓' : 'Copy poem'"></span></button>
            <button type="button" @click="BF.share(full + '\n— ' + poet)" class="rounded-full bg-emerald-700 px-4 py-2 text-sm font-semibold text-white">WhatsApp</button>
            <button type="button" @click="pick(full.split('\n\n')[0])" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Make a picture card</button>
        </div>
        <div x-show="sel" x-cloak class="mt-5 rounded-2xl border border-slate-200 bg-white p-4">
            <canvas x-ref="cv" width="1080" height="1080" class="mx-auto w-full max-w-sm rounded-xl"></canvas>
            <div class="mt-3 flex flex-wrap justify-center gap-2 text-sm font-semibold">
                <template x-for="t in themes" :key="t[0]"><button type="button" @click="theme = t; draw()" class="h-8 w-8 rounded-full border-2 border-white shadow" :style="'background:' + t[0]" :aria-label="'Colour ' + t[0]"></button></template>
                <button type="button" @click="download()" class="rounded-full bg-navy-600 px-4 py-2 text-white">Download picture</button>
                <button type="button" @click="copy(sel + '\n— ' + poet)" class="rounded-full bg-slate-200 px-4 py-2">Copy text</button>
            </div>
        </div>

        <nav class="mt-8 flex justify-between gap-3 text-sm">
            @if ($prev)<a href="{{ route('poetry.show', [$poem->poet->slug, $prev->slug]) }}" class="text-navy-600 underline">← Previous</a>@else<span></span>@endif
            @if ($next)<a href="{{ route('poetry.show', [$poem->poet->slug, $next->slug]) }}" class="text-navy-600 underline">Next →</a>@endif
        </nav>

        @if ($more->isNotEmpty())
            <h2 class="mt-10 text-lg font-bold">More {{ Str::lower($formName) }} by {{ $poem->poet->name_en }}</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach ($more as $m) @php $m->setRelation('poet', $poem->poet); @endphp @include('poetry.partials.card', ['poem' => $m]) @endforeach
            </div>
        @endif
        <p class="mt-8 rounded-lg bg-slate-100 p-4 text-xs leading-relaxed text-slate-600">
            {{ $poem->poet->name_en }} ({{ $poem->poet->years() }}): this poem is in the public domain. Text from Urdu Wikisource via the
            <a href="https://github.com/anas-rashid/divan-data" rel="nofollow noopener" target="_blank" class="underline">Divan</a> database, CC BY-SA 4.0
            @if ($poem->source_url)· <a href="{{ $poem->source_url }}" rel="nofollow noopener" target="_blank" class="underline">source page</a>@endif.
            Spotted a typing mistake? <a href="{{ route('pages.contact') }}" class="underline">Tell us</a>.
        </p>
    </div>
    <script>
        function poemCard(full, poet) {
            return {
                full, poet, sel: '', copied: false,
                themes: [['#1e3a5f', '#fff'], ['#7c2d12', '#fde68a'], ['#14532d', '#fff'], ['#f8fafc', '#0f172a'], ['#4c1d95', '#fff']],
                theme: ['#1e3a5f', '#fff'],
                async copy(t) { this.copied = await BF.copy(t); setTimeout(() => this.copied = false, 2000); },
                async pick(t) { this.sel = t; await this.$nextTick(); try { await document.fonts.load('48px "Noto Nastaliq Urdu"'); } catch (e) {} this.draw(); this.$refs.cv.scrollIntoView({ behavior: 'smooth', block: 'center' }); },
                draw() {
                    const c = this.$refs.cv, x = c.getContext('2d'), [bg, fg] = this.theme;
                    x.fillStyle = bg; x.fillRect(0, 0, 1080, 1080);
                    x.strokeStyle = fg; x.globalAlpha = .25; x.lineWidth = 4; x.strokeRect(50, 50, 980, 980); x.globalAlpha = 1;
                    x.fillStyle = fg; x.textAlign = 'center'; x.direction = 'rtl';
                    const lines = this.sel.split('\n').filter(Boolean).slice(0, 6);
                    let size = lines.length > 4 ? 44 : 54;
                    x.font = size + 'px "Noto Nastaliq Urdu", "Noto Naskh Arabic", serif';
                    while (size > 26 && Math.max(...lines.map(l => x.measureText(l).width)) > 900) { size -= 2; x.font = size + 'px "Noto Nastaliq Urdu", "Noto Naskh Arabic", serif'; }
                    const gap = size * 2.4, top = 540 - (lines.length - 1) * gap / 2;
                    lines.forEach((l, i) => x.fillText(l, 540, top + i * gap));
                    x.font = '36px "Noto Nastaliq Urdu", serif'; x.fillText('— ' + this.poet, 540, 900);
                    x.direction = 'ltr'; x.font = '600 26px Poppins, sans-serif'; x.globalAlpha = .7; x.fillText('buntyfun.com', 540, 990); x.globalAlpha = 1;
                },
                download() { const a = document.createElement('a'); a.download = 'buntyfun-poetry.png'; a.href = this.$refs.cv.toDataURL('image/png'); a.click(); },
            };
        }
    </script>
</x-layout>
