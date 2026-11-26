@props(['type' => null, 'heading' => 'Your games'])
{{-- Favourites + recently played, read from this browser only (nothing is sent to the server).
     Hidden until there is something saved. $type limits it to 'game' or 'quiz'. --}}
<section x-data="{
        type: {{ Js::from($type) }}, favs: [], recent: [],
        load() {
            const mine = (l) => l.filter((x) => !this.type || x.type === this.type);
            this.favs = mine(BF.favs());
            this.recent = mine(BF.recent()).filter((x) => !this.favs.some((f) => f.url === x.url)).slice(0, 6);
        },
    }" x-init="window.BF && load()" @bf-saved.window="load()" x-show="favs.length || recent.length" x-cloak
    {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white p-4 shadow-sm']) }} aria-label="{{ $heading }}">
    <div x-show="favs.length">
        <div class="flex items-baseline justify-between gap-3">
            <h2 class="font-semibold">★ Your favourites</h2>
            <button type="button" class="text-xs text-slate-500 underline" @click="BF.clearFavs()">Clear</button>
        </div>
        <ul class="mt-2 flex flex-wrap gap-2 text-sm">
            <template x-for="it in favs" :key="it.url">
                <li><a :href="it.url" class="inline-block rounded-full bg-sun-50 px-3 py-1 text-navy-700 hover:underline" x-text="it.title"></a></li>
            </template>
        </ul>
    </div>
    <div x-show="recent.length" :class="favs.length && 'mt-4'">
        <div class="flex items-baseline justify-between gap-3">
            <h2 class="font-semibold">Recently played</h2>
            <button type="button" class="text-xs text-slate-500 underline" @click="BF.clearRecent()">Clear</button>
        </div>
        <ul class="mt-2 flex flex-wrap gap-2 text-sm">
            <template x-for="it in recent" :key="it.url">
                <li><a :href="it.url" class="inline-block rounded-full bg-slate-100 px-3 py-1 text-navy-700 hover:bg-slate-200" x-text="it.title"></a></li>
            </template>
        </ul>
    </div>
    <p class="mt-3 text-xs text-slate-400">Saved only in this browser. No account needed.</p>
</section>
