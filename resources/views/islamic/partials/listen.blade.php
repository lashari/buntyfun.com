{{-- Listen button for one dua. Needs $dua (with ->listen from Dua::withListen) and $reciters. --}}
@php $l = $dua->listen ?? ['type' => 'voice']; @endphp
<span x-data="{ said: '', voices: [], v: '' }" class="inline-flex flex-wrap items-center gap-2"
      @if ($l['type'] === 'voice') x-init="voices = await BF.voicesFor('ar'); v = BF.store.get('voice_ar') || (voices[0] ? voices[0].id : '')" @endif>
    <button type="button"
            @click="said = await BF.listenDua({{ Js::from($l) }}, {{ Js::from(['title' => $dua->title, 'slug' => $dua->slug, 'arabic' => $dua->arabic]) }}, {{ Js::from($reciters ?? []) }})"
            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800"
            aria-label="Listen to {{ $dua->title }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg> Listen
    </button>
    @if ($l['type'] === 'voice')
        <button type="button" x-show="said === 'voice'" x-cloak @click="BF.stopVoice(); said = ''" class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold" aria-label="Stop">■ Stop</button>
    @endif
    <span class="text-xs text-slate-500">
        @if ($l['type'] === 'quran')
            Quran recitation
        @elseif ($l['type'] === 'file')
            Recorded{{ ! empty($l['speaker']) ? ' by '.$l['speaker'] : '' }}
        @else
            <template x-if="voices.length > 1">
                <label class="inline-flex items-center gap-1">Voice:
                    <select x-model="v" @change="BF.setVoice('ar', v); said = await BF.listenDua({{ Js::from($l) }}, {{ Js::from(['title' => $dua->title, 'slug' => $dua->slug, 'arabic' => $dua->arabic]) }}, [])"
                            class="max-w-[11rem] rounded border-slate-300 py-0.5 pl-1 pr-6 text-xs" aria-label="Choose Arabic voice">
                        <template x-for="o in voices" :key="o.id"><option :value="o.id" x-text="o.label" :selected="o.id === v"></option></template>
                    </select>
                </label>
            </template>
            <span x-show="said !== 'none'">Device voice (automatic, not a recitation)</span>
            <span x-show="said === 'none'" x-cloak class="text-amber-700">No Arabic voice on this device. Read the transliteration below.</span>
        @endif
    </span>
</span>
