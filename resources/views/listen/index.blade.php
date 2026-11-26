@php
    $title = 'Listen to Text — Free Text to Speech in Urdu, Arabic & English | BuntyFun';
    $description = 'Paste text or open a TXT, PDF or Word file and listen with your device’s own voice. Urdu, Arabic and English, speed control, sentence highlighting. Free, no sign-up; files stay on your device.';
    $schema = [[
        '@context' => 'https://schema.org', '@type' => 'WebApplication', 'name' => 'BuntyFun Listen to Text',
        'url' => route('listen'), 'applicationCategory' => 'UtilitiesApplication', 'operatingSystem' => 'Any (web browser)',
        'browserRequirements' => 'Requires a browser with the Web Speech API (speech synthesis)',
        'inLanguage' => ['en', 'ur', 'ar'], 'isAccessibleForFree' => true,
        'description' => 'Reads typed, pasted or uploaded text aloud with the visitor’s device voice. Files are read in the browser and never uploaded.',
    ]];
@endphp
<x-layout :title="$title" :description="$description" :breadcrumbs="['Listen to Text' => null]" :schema="$schema" :noindex="$noindex" :ads="false" :urduFont="true">
    <div class="mx-auto max-w-4xl px-4 py-8"
         x-data="listenPlayer({{ Js::from(['text' => $from['text'] ?? '', 'notice' => $from['notice'] ?? '']) }})">
        <h1 class="text-2xl font-bold text-navy-700 sm:text-3xl">Listen to Text</h1>
        <p class="mt-2 max-w-2xl text-slate-600">Type or paste text, or open a TXT, PDF or Word (DOCX) file, and your phone or computer reads it aloud. It works in English, Urdu and Arabic, with the voices <strong>your device already has</strong>. Free, no sign-up, and your files never leave your device.</p>

        @if ($from)
            <p class="mt-4 rounded-lg bg-navy-50 px-4 py-2 text-sm text-navy-700">Reading: <strong>{{ $from['title'] }}</strong> · <a href="{{ $from['url'] }}" class="underline">back to the page</a></p>
        @endif

        <template x-if="!supported">
            <div class="mt-6 rounded-xl bg-amber-50 p-4 text-amber-800" role="alert">This browser cannot speak text (no Web Speech API). Try Chrome, Edge or Safari, or update your browser.</div>
        </template>

        {{-- 1. Text --}}
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="t-text">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="t-text" class="font-semibold text-navy-700">1. Your text</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-navy-200 bg-navy-50 px-4 py-2 text-sm font-semibold text-navy-700 hover:border-brand-400 focus-within:ring-2 focus-within:ring-brand-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 16V4m0 0-4 4m4-4 4 4M4 20h16"/></svg>
                        <span x-text="busy ? 'Reading file…' : 'Open a file'"></span>
                        <input x-ref="file" type="file" accept=".txt,.pdf,.docx,text/plain,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                               class="sr-only" @change="load($event.target.files[0])" :disabled="busy" aria-describedby="file-help">
                    </label>
                    <button type="button" @click="clear()" x-show="text" x-cloak class="rounded-full border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Clear</button>
                </div>
            </div>
            <p id="file-help" class="mt-1 text-xs text-slate-500">TXT, PDF or DOCX, up to 25 MB. The file is read inside your browser and is not uploaded. Scanned PDFs (photos of pages) have no text and cannot be read.</p>
            <p x-show="fileName" x-cloak class="mt-2 text-xs text-slate-600">File: <span x-text="fileName"></span></p>

            <label for="listen-text" class="sr-only">Text to read aloud</label>
            <textarea id="listen-text" x-model="text" @input.debounce.600ms="prepare()" dir="auto" rows="8"
                      placeholder="Type or paste text here… / یہاں اردو متن لکھیں یا چسپاں کریں / اكتب النص هنا"
                      class="mt-3 w-full rounded-xl border border-slate-300 px-4 py-3 text-base leading-relaxed focus:border-brand-500 focus:outline-none"
                      :class="langsInText.some((l) => l !== 'en') ? 'font-urdu leading-loose' : ''"></textarea>
            <p class="mt-1 text-xs text-slate-500"><span x-text="chars.toLocaleString()"></span> characters<span x-show="segs.length"> · about <span x-text="minutes"></span> min of listening</span></p>

            <p x-show="notice" x-cloak class="mt-3 rounded-lg bg-sun-50 px-4 py-2 text-sm text-amber-800" x-text="notice"></p>
            <p x-show="error" x-cloak class="mt-3 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700" role="alert" x-text="error"></p>
        </section>

        {{-- 2. Player --}}
        <section class="sticky top-16 z-20 mt-5 rounded-2xl border border-navy-100 bg-navy-700 p-4 text-white shadow-lg sm:p-5" aria-labelledby="t-player" x-show="supported">
            <h2 id="t-player" class="sr-only">2. Player</h2>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="prev()" :disabled="!segs.length" class="rounded-full bg-white/10 p-2.5 hover:bg-white/20 disabled:opacity-40" aria-label="Previous sentence">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 6h2v12H6zm3.5 6 8.5 6V6z"/></svg>
                </button>
                <button type="button" @click="toggle()" class="inline-flex min-w-[8.5rem] items-center justify-center gap-2 rounded-full bg-brand-600 px-6 py-3 font-bold text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-white"
                        :aria-label="status === 'playing' ? 'Pause' : (status === 'paused' ? 'Resume' : 'Play')">
                    <svg x-show="status !== 'playing'" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                    <svg x-show="status === 'playing'" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 5h4v14H6zm8 0h4v14h-4z"/></svg>
                    <span x-text="status === 'playing' ? 'Pause' : (status === 'paused' ? 'Resume' : 'Play')">Play</span>
                </button>
                <button type="button" @click="next()" :disabled="!segs.length" class="rounded-full bg-white/10 p-2.5 hover:bg-white/20 disabled:opacity-40" aria-label="Next sentence">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 6h2v12h-2zM6 18l8.5-6L6 6z"/></svg>
                </button>
                <button type="button" @click="stop()" :disabled="status === 'idle'" class="rounded-full bg-white/10 px-4 py-2.5 text-sm font-semibold hover:bg-white/20 disabled:opacity-40" aria-label="Stop">■ Stop</button>

            </div>

            {{-- Everything is automatic (language per sentence, best voice, 1x). These are optional extras, closed by default. --}}
            <details class="mt-3 text-sm">
                <summary class="cursor-pointer select-none text-navy-100 hover:text-white">More options</summary>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                <label class="inline-flex items-center gap-2 text-sm">Speed
                    <select x-model.number="rate" @change="setRate()" class="rounded-lg border-0 bg-white/10 py-1.5 pl-2 pr-7 text-sm text-white [&>option]:text-slate-900">
                        <template x-for="r in rates" :key="r"><option :value="r" x-text="r + 'x'" :selected="r === rate"></option></template>
                    </select>
                </label>
                <label class="inline-flex items-center gap-2 text-sm">Language
                    <select x-model="lang" @change="prepare()" class="rounded-lg border-0 bg-white/10 py-1.5 pl-2 pr-7 text-sm text-white [&>option]:text-slate-900">
                        <option value="auto">Auto (mixed)</option>
                        <option value="en">English</option>
                        <option value="ur">Urdu اردو</option>
                        <option value="ar">Arabic العربية</option>
                    </select>
                </label>
                <div class="grid w-full gap-2 text-sm sm:grid-cols-3">
                <template x-for="l in ['en', 'ur', 'ar']" :key="l">
                    <label class="flex min-w-0 items-center gap-2" x-show="langsInText.includes(l) || lang === l">
                        <span class="shrink-0" x-text="{ en: 'English', ur: 'Urdu', ar: 'Arabic' }[l] + ' voice'"></span>
                        <select x-show="voices[l].length" x-model="chosen[l]" @change="setVoice(l)" class="min-w-0 flex-1 truncate rounded-lg border-0 bg-white/10 py-1.5 pl-2 pr-7 text-sm text-white [&>option]:text-slate-900"
                                :aria-label="'Choose ' + { en: 'English', ur: 'Urdu', ar: 'Arabic' }[l] + ' voice'">
                            <template x-for="o in voices[l]" :key="o.id"><option :value="o.id" x-text="o.label" :selected="o.id === chosen[l]"></option></template>
                        </select>
                        <span x-show="!voices[l].length" class="text-amber-200">none on this device</span>
                    </label>
                </template>
            </div>
                </div>
            </details>

            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-white/15" aria-hidden="true">
                <div class="h-full bg-brand-400 transition-all" :style="'width:' + (segs.length ? Math.round(((status === 'done' ? segs.length : i) / segs.length) * 100) : 0) + '%'"></div>
            </div>
            <p class="mt-2 text-xs text-navy-100" aria-live="polite"
               x-text="!segs.length ? 'Add text to start.' : (status === 'done' ? 'Finished.' : (status === 'playing' ? 'Reading' : (status === 'paused' ? 'Paused at' : 'Ready:')) + ' sentence ' + (i + 1) + ' of ' + segs.length)"></p>
            <p x-show="loadingUrdu" x-cloak class="mt-2 text-xs text-navy-100">Loading the Urdu voice (first time only)…</p>
            <p x-show="missing.length" x-cloak class="mt-2 rounded-lg bg-amber-100 px-3 py-2 text-sm text-amber-900">
                No <span x-text="missing.map((l) => ({ en: 'English', ur: 'Urdu', ar: 'Arabic' }[l])).join(' / ')"></span> voice on this device, so those lines are skipped.
            </p>
        </section>

        {{-- 3. Reading pane with highlighting --}}
        <section x-show="segs.length" x-cloak class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="t-read">
            <h2 id="t-read" class="text-sm font-semibold text-slate-500">3. Follow along <span class="font-normal">(tap a sentence to read from there)</span></h2>
            <div x-ref="pane" class="mt-3 max-h-[60vh] space-y-4 overflow-y-auto text-lg leading-relaxed text-slate-800">
                <template x-for="(p, pi) in paras" :key="pi">
                    <p dir="auto" :class="p[0] && p[0].lang !== 'en' ? 'font-urdu leading-[2.4] text-xl' : ''"><template x-for="s in p" :key="s.n"><span :data-seg="s.n" :dir="s.lang === 'en' ? 'ltr' : 'rtl'" :lang="s.lang" @click="jump(s.n)"
                              class="cursor-pointer rounded px-0.5 transition-colors hover:bg-brand-50"
                              :class="s.n === i && status !== 'idle' ? 'bg-sun-400/50 text-navy-900' : ''" x-text="s.text"></span></template></p>
                </template>
            </div>
        </section>

        @if ($picks)
        <section class="mt-8" aria-labelledby="t-picks">
            <h2 id="t-picks" class="text-lg font-bold text-navy-700">Listen to BuntyFun content</h2>
            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                @foreach ($picks as $group => $items)
                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-slate-600">{{ $group }}</h3>
                        <ul class="mt-2 space-y-1 text-sm">
                            @foreach ($items as [$label, $key])
                                <li><a href="{{ route('listen', ['from' => $key]) }}" rel="nofollow" class="text-navy-600 hover:text-brand-700 hover:underline" dir="auto">{{ $label }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-sm text-slate-500">For the Quran, use the real reciters on <a href="{{ route('quran.index') }}" class="text-navy-600 underline">Quran with audio</a>: a computer voice is not a substitute for recitation, so this page does not read Quran verses for you.</p>
        </section>
        @endif

        <section class="prose-page mt-10 max-w-3xl" aria-labelledby="t-help">
            <h2 id="t-help">How it works</h2>
            <p>Press Play: the page picks the language of each sentence and the best voice on your device automatically. Nothing to set up, and BuntyFun does not send your text anywhere.</p>
            <ul>
                <li><strong>English</strong> and <strong>Arabic</strong> voices are built into almost every phone and computer.</li>
                <li><strong>Urdu</strong> voices are built into most Android phones and Microsoft Edge on Windows. Where a device has none (many iPhones and computers), BuntyFun’s built-in Urdu voice reads it automatically. It is a basic computer voice (eSpeak NG, open source) and sounds robotic, but it reads real Urdu, not another language.</li>
                <li>Voices are labelled with the region the device reports (for example Pakistan or India); we never call a voice Pakistani unless the device says so.</li>
            </ul>
            <h2>Good to know</h2>
            <ul>
                <li><strong>Pause</strong> stops at the current sentence; <strong>Resume</strong> starts that sentence again. This works the same on every browser.</li>
                <li>Mixed text (English with Urdu or Arabic) is read with the right voice for each part when <em>Language</em> is “Auto”.</li>
                <li>Urdu and Arabic PDFs sometimes contain broken text (common with InPage exports). Check the text after opening a file and fix it if needed.</li>
                <li>Computer voices are not a substitute for learning Quran recitation or duas from a teacher.</li>
            </ul>
            <p>Related: <a href="{{ route('duas.index') }}">masnoon duas</a> (with Listen buttons) · <a href="{{ route('poetry.index') }}">Urdu poetry</a> · <a href="{{ route('urdu-content.index') }}">Urdu corner</a> · <a href="{{ route('quran.index') }}">Quran with real reciters</a></p>
        </section>
    </div>
</x-layout>
