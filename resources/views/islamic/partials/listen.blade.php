{{-- Listen button for one dua. Needs $dua (with ->listen from Dua::withListen) and $reciters. --}}
@php $l = $dua->listen ?? ['type' => 'voice']; @endphp
<span x-data="{ said: '' }" class="inline-flex flex-wrap items-center gap-2">
    <button type="button"
            @click="said = BF.listenDua({{ Js::from($l) }}, {{ Js::from(['title' => $dua->title, 'slug' => $dua->slug, 'arabic' => $dua->arabic]) }}, {{ Js::from($reciters ?? []) }})"
            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800"
            aria-label="Listen to {{ $dua->title }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg> Listen
    </button>
    <span class="text-xs text-slate-500">
        @if ($l['type'] === 'quran')
            Quran recitation
        @elseif ($l['type'] === 'file')
            Recorded{{ ! empty($l['speaker']) ? ' by '.$l['speaker'] : '' }}
        @else
            <span x-show="said !== 'none'">Device voice (automatic, not a recitation)</span>
            <span x-show="said === 'none'" x-cloak class="text-amber-700">Your device has no Arabic voice. Read the transliteration instead.</span>
        @endif
    </span>
</span>
