<x-layout :title="$game->title.' — Play Free Online, Rules & Tips | BuntyFun'" :breadcrumbs="['Games' => route('games.index'), $game->title => null]" :description="\App\Content\GameGuides::for($game->slug)['summary'].' Free, no sign-up, works on phones.'">
<div class="mx-auto max-w-md px-4 py-8 text-center"
     x-data="{
        bank: {{ Js::from(\App\Content\WordLists::HANGMAN) }}, cat: 'Animals', word: '', hint: '', guessed: [], lives: 6, wins: 0, streak: 0,
        init() { this.streak = BF.store.get('hangman_streak', 0); this.newWord(); },
        newWord() {
            const [w, h] = BF.shuffle(this.bank[this.cat])[0];
            this.word = w; this.hint = h; this.guessed = []; this.lives = 6;
            BF.played({ url: location.pathname, title: 'Hangman', type: 'game' });
        },
        get misses() { return this.guessed.filter(l => !this.word.includes(l)).length },
        get won() { return this.word && [...this.word].every(l => this.guessed.includes(l)) },
        get lost() { return this.misses >= this.lives },
        guess(l) {
            if (this.won || this.lost || this.guessed.includes(l)) return;
            this.guessed.push(l);
            if (this.won) { this.streak++; BF.store.set('hangman_streak', this.streak); BF.best('hangman_streak', this.streak); }
            if (this.lost) { this.streak = 0; BF.store.set('hangman_streak', 0); }
        },
     }" @keydown.window="if (/^[a-z]$/i.test($event.key)) guess($event.key.toUpperCase())">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ $game->title }}</h1>
    <div class="mt-3 flex flex-wrap justify-center gap-2">
        <template x-for="c in Object.keys(bank)" :key="c">
            <button type="button" @click="cat = c; newWord()" :class="cat === c ? 'bg-navy-600 text-white' : 'bg-slate-100'" class="rounded-full px-3 py-1 text-sm font-semibold" x-text="c"></button>
        </template>
    </div>
    <svg viewBox="0 0 120 120" class="mx-auto mt-4 h-36 w-36" aria-label="Wrong guesses" role="img">
        <path d="M10 110h60M30 110V10h50v14" stroke="#64748b" stroke-width="4" fill="none" stroke-linecap="round"/>
        <circle x-show="misses > 0" cx="80" cy="34" r="10" stroke="#e46a00" stroke-width="4" fill="none"/>
        <path x-show="misses > 1" d="M80 44v30" stroke="#e46a00" stroke-width="4"/>
        <path x-show="misses > 2" d="M80 52l-14 12" stroke="#e46a00" stroke-width="4" stroke-linecap="round"/>
        <path x-show="misses > 3" d="M80 52l14 12" stroke="#e46a00" stroke-width="4" stroke-linecap="round"/>
        <path x-show="misses > 4" d="M80 74l-12 18" stroke="#e46a00" stroke-width="4" stroke-linecap="round"/>
        <path x-show="misses > 5" d="M80 74l12 18" stroke="#e46a00" stroke-width="4" stroke-linecap="round"/>
    </svg>
    <p class="text-sm text-slate-600">Hint: <span class="font-semibold" x-text="hint"></span> · Lives left: <span x-text="lives - misses"></span> · Streak 🔥 <span x-text="streak"></span></p>
    <p class="mt-4 flex flex-wrap justify-center gap-1.5 text-3xl font-extrabold tracking-wide">
        <template x-for="(l, k) in word.split('')" :key="k"><span class="w-7 border-b-4 border-slate-300 sm:w-8" :class="lost && !guessed.includes(l) ? 'text-red-500' : ''" x-text="guessed.includes(l) || lost ? l : ' '"></span></template>
    </p>
    <div x-show="won || lost" x-cloak class="mt-5 rounded-2xl p-4" :class="won ? 'bg-emerald-50' : 'bg-red-50'">
        <p class="text-lg font-bold" x-text="won ? 'You got it! 🎉' : 'The word was ' + word"></p>
        <div class="mt-3 flex justify-center gap-2">
            <button type="button" @click="newWord()" class="rounded-full bg-brand-600 px-5 py-2 font-semibold text-white">Next word</button>
            <button type="button" x-show="won" @click="BF.share('I am on a ' + streak + '-word streak in BuntyFun Hangman 🔤 Can you beat it?')" class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white">Share</button>
        </div>
    </div>
    <div class="mt-5 grid grid-cols-7 gap-1.5 sm:grid-cols-9">
        <template x-for="l in 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('')" :key="l">
            <button type="button" @click="guess(l)" :disabled="guessed.includes(l) || won || lost"
                    class="rounded-lg py-2.5 text-lg font-bold" :class="!guessed.includes(l) ? 'bg-white border border-slate-300 active:bg-brand-50' : (word.includes(l) ? 'bg-emerald-200 text-emerald-900' : 'bg-slate-200 text-slate-400')" x-text="l"></button>
        </template>
    </div>
</div>
@include('games.partials.guide')
</x-layout>
