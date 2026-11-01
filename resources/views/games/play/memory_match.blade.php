<x-layout :title="$game->title.' — BuntyFun'" description="Flip and match the pairs — free browser memory game.">
    <div class="mx-auto max-w-md px-4 py-10 text-center"
         x-data="{
             icons: ['🎉','🎈','🎂','🎁','🎆','🎶','❤️','⭐'],
             cards: [],
             flipped: [],
             matched: [],
             moves: 0,
             busy: false,
             setup() {
                 const pairs = [...this.icons, ...this.icons]
                     .map((icon, i) => ({ id: i, icon }))
                     .sort(() => Math.random() - 0.5);
                 this.cards = pairs;
                 this.flipped = [];
                 this.matched = [];
                 this.moves = 0;
             },
             flip(i) {
                 if (this.busy || this.flipped.includes(i) || this.matched.includes(i)) return;
                 this.flipped.push(i);
                 if (this.flipped.length === 2) {
                     this.moves++;
                     this.busy = true;
                     const [a, b] = this.flipped;
                     if (this.cards[a].icon === this.cards[b].icon) {
                         this.matched.push(a, b);
                         this.flipped = [];
                         this.busy = false;
                     } else {
                         setTimeout(() => { this.flipped = []; this.busy = false; }, 700);
                     }
                 }
             },
         }" x-init="setup()">
        <h1 class="text-2xl font-bold">{{ $game->title }}</h1>
        <p class="mt-2 text-sm text-slate-500">
            Moves: <span x-text="moves"></span>
            <span x-show="matched.length === cards.length && cards.length" x-cloak class="font-semibold text-emerald-600"> — Solved!</span>
        </p>

        <div class="mx-auto mt-6 grid grid-cols-4 gap-2">
            <template x-for="(card, i) in cards" :key="card.id">
                <button type="button" @click="flip(i)"
                        class="flex h-16 w-16 items-center justify-center rounded-lg border border-slate-200 bg-white text-2xl hover:bg-slate-50">
                    <span x-show="flipped.includes(i) || matched.includes(i)" x-text="card.icon"></span>
                </button>
            </template>
        </div>

        <button type="button" @click="setup()" class="mt-6 rounded-full bg-violet-600 px-6 py-2 font-semibold text-white hover:bg-violet-700">
            New Game
        </button>
        <a href="{{ route('games.index') }}" class="mt-4 block text-sm text-slate-500 hover:underline">More games</a>
    </div>
</x-layout>
