{{-- Site-wide audio bar (store: resources/js/player.js). Shown once something is played. --}}
<div x-data x-cloak x-show="$store.player && $store.player.visible" x-transition.opacity
     class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 bg-white/95 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] backdrop-blur"
     role="region" aria-label="Audio player">
    <template x-if="$store.player">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center gap-x-3 gap-y-2 px-3 py-2 sm:flex-nowrap sm:px-4">
            <div class="min-w-0 flex-1 basis-full sm:basis-auto">
                <p class="truncate text-sm font-semibold text-navy-800" x-text="$store.player.current ? $store.player.current.title : ''"></p>
                <p class="truncate text-xs text-slate-500" x-text="$store.player.error || ($store.player.current ? $store.player.current.subtitle : '')" :class="$store.player.error && 'text-red-600'" aria-live="polite"></p>
            </div>

            <div class="flex items-center gap-1">
                <button type="button" @click="$store.player.prev()" class="grid h-11 w-11 place-items-center rounded-full hover:bg-slate-100" aria-label="Previous">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 6h2v12H6zm3.5 6 8.5 6V6z"/></svg>
                </button>
                <button type="button" @click="$store.player.toggle()" class="grid h-12 w-12 place-items-center rounded-full bg-emerald-700 text-white hover:bg-emerald-800"
                        :aria-label="$store.player.playing ? 'Pause' : 'Play'">
                    <svg x-show="$store.player.loading" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" stroke-dasharray="40 20"/></svg>
                    <svg x-show="!$store.player.loading && !$store.player.playing" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                    <svg x-show="!$store.player.loading && $store.player.playing" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 5h4v14H6zm8 0h4v14h-4z"/></svg>
                </button>
                <button type="button" @click="$store.player.next()" class="grid h-11 w-11 place-items-center rounded-full hover:bg-slate-100" aria-label="Next">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 18l8.5-6L6 6zM16 6h2v12h-2z"/></svg>
                </button>
            </div>

            <div class="flex min-w-[10rem] flex-1 items-center gap-2 text-xs tabular-nums text-slate-500">
                <span x-text="$store.player.timeText"></span>
                <input type="range" min="0" max="100" step="0.1" :value="$store.player.progress" @input="$store.player.seek($event.target.value)"
                       class="h-2 w-full cursor-pointer accent-emerald-700" aria-label="Seek" :aria-valuetext="$store.player.timeText + ' of ' + $store.player.durationText">
                <span x-text="$store.player.durationText"></span>
            </div>

            <div class="flex items-center gap-2">
                <label class="sr-only" for="bf-repeat">Repeat</label>
                <select id="bf-repeat" class="rounded-lg border-slate-300 py-1 pl-2 pr-7 text-xs" :value="$store.player.repeatEach" @change="$store.player.repeatEach = Number($event.target.value)" title="Repeat each track">
                    <option value="1">Repeat: off</option><option value="2">Repeat ×2</option><option value="3">Repeat ×3</option><option value="5">Repeat ×5</option><option value="10">Repeat ×10</option><option value="0">Repeat ∞</option>
                </select>
                <template x-if="$store.player.allowSpeed">
                    <select class="rounded-lg border-slate-300 py-1 pl-2 pr-7 text-xs" :value="$store.player.rate" @change="$store.player.setRate($event.target.value)" aria-label="Playback speed">
                        <option value="0.75">0.75×</option><option value="1">1×</option><option value="1.25">1.25×</option><option value="1.5">1.5×</option>
                    </select>
                </template>
                <button type="button" @click="$store.player.toggleMute()" class="grid h-9 w-9 place-items-center rounded-full hover:bg-slate-100" :aria-label="$store.player.muted ? 'Unmute' : 'Mute'">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 9v6h4l5 5V4L8 9z"/><path x-show="!$store.player.muted" d="M16 8a5 5 0 0 1 0 8" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                </button>
                <input type="range" min="0" max="1" step="0.05" :value="$store.player.volume" @input="$store.player.setVolume($event.target.value)" class="hidden w-20 accent-emerald-700 md:block" aria-label="Volume">
                <button type="button" @click="$store.player.close()" class="grid h-9 w-9 place-items-center rounded-full text-slate-400 hover:bg-slate-100" aria-label="Close player">✕</button>
            </div>
        </div>
    </template>
</div>
