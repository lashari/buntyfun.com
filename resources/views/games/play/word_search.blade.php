<x-layout :title="$game->title.' — Play Free Online, Rules & Tips | BuntyFun'" :breadcrumbs="['Games' => route('games.index'), $game->title => null]" :description="\App\Content\GameGuides::for($game->slug)['summary'].' Free, no sign-up, works on phones.'">
<div class="mx-auto max-w-xl px-3 py-8 text-center"
     x-data="{
        themes: {{ Js::from(\App\Content\WordLists::WORD_SEARCH) }}, theme: 'Animals', size: 10, level: 'easy',
        grid: [], words: [], found: [], foundCells: {}, start: null, hover: null, secs: 0, timer: null, won: false, best: null,
        dirs() { return this.level === 'easy' ? [[0,1],[1,0]] : (this.level === 'medium' ? [[0,1],[1,0],[1,1],[-1,1]] : [[0,1],[1,0],[1,1],[-1,1],[0,-1],[-1,0],[-1,-1],[1,-1]]) },
        build() {
            this.size = window.innerWidth < 400 ? 9 : 10;
            const n = this.size, want = this.level === 'easy' ? 6 : 8;
            for (let attempt = 0; attempt < 30; attempt++) {
                const g = Array.from({ length: n }, () => Array(n).fill(''));
                const placed = [];
                for (const w of BF.shuffle(this.themes[this.theme].filter(w => w.length <= n))) {
                    if (placed.length >= want) break;
                    for (let t = 0; t < 80; t++) {
                        const [dr, dc] = BF.shuffle(this.dirs())[0];
                        const r = Math.floor(Math.random() * n), c = Math.floor(Math.random() * n);
                        const er = r + dr * (w.length - 1), ec = c + dc * (w.length - 1);
                        if (er < 0 || er >= n || ec < 0 || ec >= n) continue;
                        let ok = true;
                        for (let k = 0; k < w.length; k++) { const ch = g[r + dr * k][c + dc * k]; if (ch && ch !== w[k]) { ok = false; break; } }
                        if (!ok) continue;
                        for (let k = 0; k < w.length; k++) g[r + dr * k][c + dc * k] = w[k];
                        placed.push(w); break;
                    }
                }
                if (placed.length >= want) {
                    const abc = 'ABCDEFGHIJKLMNOPRSTUWY';
                    this.grid = g.map(row => row.map(ch => ch || abc[Math.floor(Math.random() * abc.length)]));
                    this.words = placed.sort(); break;
                }
            }
            this.found = []; this.foundCells = {}; this.start = null; this.won = false; this.secs = 0;
            clearInterval(this.timer); const t0 = Date.now(); this.timer = setInterval(() => this.secs = Math.floor((Date.now() - t0) / 1000), 500);
            BF.played({ url: location.pathname, title: 'Word Search', type: 'game' });
        },
        line(a, b) {
            if (!a || !b) return [];
            const dr = Math.sign(b[0] - a[0]), dc = Math.sign(b[1] - a[1]);
            const len = Math.max(Math.abs(b[0] - a[0]), Math.abs(b[1] - a[1]));
            if (!(a[0] === b[0] || a[1] === b[1] || Math.abs(b[0] - a[0]) === Math.abs(b[1] - a[1]))) return [];
            return Array.from({ length: len + 1 }, (_, k) => [a[0] + dr * k, a[1] + dc * k]);
        },
        tap(r, c) {
            if (this.won) return;
            if (!this.start) { this.start = [r, c]; return; }
            const cells = this.line(this.start, [r, c]);
            const word = cells.map(([y, x]) => this.grid[y][x]).join('');
            const rev = word.split('').reverse().join('');
            const hit = this.words.find(w => !this.found.includes(w) && (w === word || w === rev));
            if (hit) { this.found.push(hit); cells.forEach(([y, x]) => this.foundCells[y + '-' + x] = true); }
            this.start = null;
            if (this.found.length === this.words.length) { this.won = true; clearInterval(this.timer); this.best = BF.best('wordsearch_' + this.level, this.secs, true); }
        },
        cls(r, c) {
            const k = r + '-' + c;
            if (this.start && this.start[0] === r && this.start[1] === c) return 'bg-brand-500 text-white';
            if (this.start && this.hover && this.line(this.start, this.hover).some(([y, x]) => y === r && x === c)) return 'bg-brand-100';
            return this.foundCells[k] ? 'bg-emerald-200 text-emerald-900' : 'bg-white';
        },
     }" x-init="build()">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ $game->title }}</h1>
    <div class="mt-4 flex flex-wrap justify-center gap-2 text-sm">
        <select x-model="theme" @change="build()" class="rounded-full border border-slate-300 px-3 py-1.5" aria-label="Theme">
            <template x-for="t in Object.keys(themes)" :key="t"><option :value="t" x-text="t"></option></template>
        </select>
        <select x-model="level" @change="build()" class="rounded-full border border-slate-300 px-3 py-1.5" aria-label="Level">
            <option value="easy">Easy: across and down</option><option value="medium">Medium: + diagonals</option><option value="hard">Hard: all 8 directions</option>
        </select>
    </div>
    <p class="mt-3 text-sm text-slate-600">Tap the first letter, then the last letter of a word. ⏱ <span x-text="secs"></span>s · <span x-text="found.length"></span>/<span x-text="words.length"></span> found</p>
    <div class="mx-auto mt-3 grid select-none gap-0.5 rounded-xl bg-slate-200 p-0.5" :style="`grid-template-columns: repeat(${size}, minmax(0, 1fr)); max-width: ${size * 2.6}rem`">
        <template x-for="(row, r) in grid" :key="r">
            <template x-for="(ch, c) in row" :key="r + '-' + c">
                <button type="button" @click="tap(r, c)" @mouseenter="hover = [r, c]" :class="cls(r, c)"
                        class="aspect-square rounded text-base font-bold sm:text-lg" x-text="ch" :aria-label="`Row ${r + 1} column ${c + 1}: ${ch}`"></button>
            </template>
        </template>
    </div>
    <ul class="mt-4 flex flex-wrap justify-center gap-2 text-sm font-semibold">
        <template x-for="w in words" :key="w"><li class="rounded-full px-3 py-1" :class="found.includes(w) ? 'bg-emerald-100 text-emerald-800 line-through' : 'bg-slate-100'" x-text="w"></li></template>
    </ul>
    <div x-show="won" x-cloak class="mt-5 rounded-2xl bg-emerald-50 p-5">
        <p class="text-xl font-bold text-emerald-800">All words found in <span x-text="secs"></span> seconds!</p>
        <p class="text-sm" x-text="best && best.isNew ? 'New best time!' : 'Best time: ' + (best ? best.best : '') + 's'"></p>
        <div class="mt-3 flex justify-center gap-2">
            <button type="button" @click="build()" class="rounded-full bg-brand-600 px-5 py-2 font-semibold text-white">New puzzle</button>
            <button type="button" @click="BF.share('I found all ' + words.length + ' ' + theme + ' words in ' + secs + 's on BuntyFun Word Search 🔎')" class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white">Share</button>
        </div>
    </div>
    <button type="button" x-show="!won" @click="build()" class="mt-5 text-sm text-slate-500 underline">New puzzle</button>
</div>
@include('games.partials.guide')
</x-layout>
