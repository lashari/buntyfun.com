@props(['text', 'lang' => 'en', 'full' => false, 'label' => null])
{{--
    Read text aloud with the visitor's own device voice (Web Speech API) in the right language.
    Never uses a voice of another language. full=true also shows the Voice menu (choice is saved per language, site-wide).
--}}
@php
    $lang = in_array($lang, ['en', 'ur', 'ar'], true) ? $lang : 'en';
    $names = ['en' => 'English', 'ur' => 'Urdu', 'ar' => 'Arabic'];
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)));
@endphp
@if ($text !== '')
<span x-data="{ said: '', voices: [], v: '', lang: '{{ $lang }}', text: {{ Js::from($text) }},
               async go() { this.said = await BF.say(this.text, this.lang); },
               stop() { BF.stopVoice(); this.said = ''; } }"
      @if ($full) x-init="voices = await BF.voicesFor(lang); v = BF.store.get('voice_' + lang) || (voices[0] ? voices[0].id : '')" @endif
      {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-center gap-2']) }} dir="ltr">
    <button type="button" @click.prevent.stop="go()"
            class="inline-flex items-center gap-1.5 rounded-full {{ $full ? 'bg-emerald-700 px-4 py-2 text-sm text-white hover:bg-emerald-800' : 'border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50' }} font-semibold"
            aria-label="{{ $label ?: 'Listen' }} ({{ $names[$lang] }} voice)">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 9v6h4l5 5V4L8 9z"/><path d="M16 8a5 5 0 0 1 0 8" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Listen
    </button>
    <button type="button" x-show="said === 'voice'" x-cloak @click.prevent.stop="stop()" class="rounded-full border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold" aria-label="Stop reading">■ Stop</button>
    @if ($full)
        <template x-if="voices.length > 1">
            <label class="inline-flex items-center gap-1 text-xs text-slate-500">Voice:
                <select x-model="v" @change="BF.setVoice(lang, v); go()" class="max-w-[11rem] rounded border-slate-300 py-0.5 pl-1 pr-6 text-xs" aria-label="Choose {{ $names[$lang] }} voice">
                    <template x-for="o in voices" :key="o.id"><option :value="o.id" x-text="o.label" :selected="o.id === v"></option></template>
                </select>
            </label>
        </template>
        <span class="text-xs text-slate-500" x-show="said !== 'none'">Device voice (automatic)</span>
    @endif
    <span x-show="said === 'none'" x-cloak class="text-xs text-amber-700">No {{ $names[$lang] }} voice on this device{{ $lang === 'ur' ? ' (add Urdu in your phone’s text-to-speech settings)' : '' }}.</span>
</span>
@endif
