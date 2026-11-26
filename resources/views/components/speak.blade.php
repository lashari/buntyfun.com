@props(['text' => '', 'lang' => 'en', 'full' => false, 'label' => null, 'parts' => null, 'player' => null])
{{--
    Listen button. Urdu text with a pre-generated recording (App\Support\UrduVoice, female + male) plays that;
    otherwise the visitor's device voice of the right language (never another language's voice).
    full=true also shows the voice menu. Choices are saved site-wide.
    parts: [['text' => …, 'lang' => 'ar'|'en'|'ur'], …] read one after another; a page may filter them by meaning language.
--}}
@php
    $lang = in_array($lang, ['en', 'ur', 'ar'], true) ? $lang : 'en';
    $names = ['en' => 'English', 'ur' => 'Urdu', 'ar' => 'Arabic'];
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)));
    $parts = $parts ? array_values(array_filter($parts, fn ($p) => trim((string) ($p['text'] ?? '')) !== '')) : null;
    if ($parts) {
        $parts = array_map(fn ($p) => $p + (($p['lang'] ?? '') === 'ur' ? ['src' => \App\Support\UrduVoice::src(trim(preg_replace('/\s+/u', ' ', $p['text'])))] : []), $parts);
        $recorded = collect($parts)->contains(fn ($p) => ! empty($p['src']));
    } else {
        $src = $lang === 'ur' && $text !== '' ? \App\Support\UrduVoice::src($text) : null;
        $parts = $src ? [['text' => $text, 'lang' => 'ur', 'src' => $src]] : null;
        $recorded = (bool) $src;
    }
@endphp
@if ($text !== '' || $parts)
<span x-data="{ said: '', can: true, failed: false, run: -1, voices: [], v: '', g: 'female', lang: '{{ $lang }}', text: {{ Js::from($text) }}, parts: {{ Js::from($parts) }},
               async go() {
                   BF.stopVoice();
                   this.failed = false;
                   if (!this.parts) { this.said = await BF.say(this.text, this.lang); }
                   else {
                       const m = BF.store.get('meaning_lang', 'en');
                       this.said = await BF.speakSeq(this.parts.filter((p) => this.parts.length === 1 || p.lang === 'ar' || m === 'both' || p.lang === m));
                   }
                   this.run = BF.currentSpeech();
                   if (this.said === 'none') this.can = false; // nothing can read this here: hide the button, no error text
               },
               stop() { BF.stopVoice(); this.said = ''; } }"
      x-init="g = BF.urduVoice(); @if (! $recorded) BF.canSpeak(lang).then((ok) => { can = ok; }); @endif @if ($full && ! $recorded) voices = await BF.voicesFor(lang); v = BF.store.get('voice_' + lang) || (voices[0] ? voices[0].id : ''); @endif"
      @bf-voice-failed.window="if ($event.detail.id === run) { failed = true; said = ''; }"
      {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-center gap-2']) }} dir="ltr">
    <button type="button" x-show="can" @click.prevent.stop="go()"
            class="inline-flex items-center gap-1.5 rounded-full {{ $full ? 'bg-emerald-700 px-4 py-2 text-sm text-white hover:bg-emerald-800' : 'border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50' }} font-semibold"
            aria-label="{{ $label ?: 'Listen' }} ({{ $names[$lang] }} voice)">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 9v6h4l5 5V4L8 9z"/><path d="M16 8a5 5 0 0 1 0 8" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Listen
    </button>
    <button type="button" x-show="said === 'voice' || said === 'audio'" x-cloak @click.prevent.stop="stop()" class="rounded-full border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold" aria-label="Stop reading">■ Stop</button>
    @if ($recorded)
        <label class="inline-flex items-center gap-1 text-xs text-slate-500">
            <span class="{{ $full ? '' : 'sr-only' }}">Urdu voice:</span>
            <select x-model="g" @change="BF.urduVoice(g); go()" class="rounded border-slate-300 py-0.5 pl-1 pr-6 text-xs" aria-label="Urdu voice: female or male">
                @foreach (\App\Support\UrduVoice::VOICES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
            </select>
        </label>
    @elseif ($full)
        <template x-if="can && voices.length > 1">
            <label class="inline-flex items-center gap-1 text-xs text-slate-500">Voice:
                <select x-model="v" @change="BF.setVoice(lang, v); go()" class="max-w-[11rem] rounded border-slate-300 py-0.5 pl-1 pr-6 text-xs" aria-label="Choose {{ $names[$lang] }} voice">
                    <template x-for="o in voices" :key="o.id"><option :value="o.id" x-text="o.label" :selected="o.id === v"></option></template>
                </select>
            </label>
        </template>
        <span class="text-xs text-slate-500" x-show="can">Device voice (automatic)</span>
    @endif
    @if ($full && $recorded)<span class="text-xs text-slate-400">Computer voice (AI4Bharat Indic Parler-TTS)</span>@endif
    @if ($player)<a href="{{ route('listen', ['from' => $player]) }}" rel="nofollow" class="text-xs font-semibold text-navy-600 underline hover:text-brand-700">Open in Listen player (speed, highlighting)</a>@endif
    {{-- Shown only after a real failure; the text is set by script, so it is not in the page HTML (crawlers, reader mode, copy). --}}
    <span x-show="failed" x-cloak class="text-xs text-amber-700" role="status" x-text="failed ? 'The {{ $names[$lang] }} voice did not load. Tap Listen to try again.' : ''"></span>
</span>
@endif
