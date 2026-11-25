{{-- Pronunciation button: reviewed recording if published, else the device's Arabic voice. --}}
@php $src = $name->relationLoaded('audio') && $name->audio ? $name->audio->src() : null; @endphp
@if ($name->name_ar)
<span x-data="{ r: '' }" class="inline-flex items-center gap-1">
    <button type="button" @click.prevent.stop="r = BF.playName({{ Js::from($src) }}, {{ Js::from($name->name_ar) }}, {{ Js::from($name->name_en) }})"
            class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-800 hover:bg-emerald-100"
            aria-label="Hear how to pronounce {{ $name->name_en }}" title="{{ $src ? 'Play recorded pronunciation' : 'Play with your device voice (automatic)' }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 9v6h4l5 5V4L8 9z"/><path d="M16 8a5 5 0 0 1 0 8" stroke="currentColor" stroke-width="2" fill="none"/></svg>
    </button>
    @if ($full ?? false)
        <span class="text-xs text-slate-500" x-show="r !== 'none'">{{ $src ? 'Recorded pronunciation' : 'Device voice (automatic)' }}</span>
        <span class="text-xs text-amber-700" x-show="r === 'none'" x-cloak>No Arabic voice on this device — see the guide below.</span>
    @endif
</span>
@endif
