<x-layout :title="$game->title.' — Play Free Online, Rules & Tips | BuntyFun'" :breadcrumbs="['Games' => route('games.index'), $game->title => null]" :description="\App\Content\GameGuides::for($game->slug)['summary'].' Free, no sign-up, works on phones.'">
<div class="mx-auto max-w-md px-3 py-8 text-center"
     x-data="{
        level: 'easy', puzzle: [], board: [], solution: [], sel: null, secs: 0, timer: null, mistakes: 0, won: false, best: null, check: true,
        ok(b, i, v) {
            const r = Math.floor(i / 9), c = i % 9, br = r - r % 3, bc = c - c % 3;
            for (let k = 0; k < 9; k++) {
                if (b[r * 9 + k] === v || b[k * 9 + c] === v) return false;
                if (b[(br + Math.floor(k / 3)) * 9 + bc + k % 3] === v) return false;
            }
            return true;
        },
        fill(b) {
            const i = b.indexOf(0); if (i < 0) return true;
            for (const v of BF.shuffle([1,2,3,4,5,6,7,8,9])) { if (this.ok(b, i, v)) { b[i] = v; if (this.fill(b)) return true; b[i] = 0; } }
            return false;
        },
        count(b, limit = 2) {
            const i = b.indexOf(0); if (i < 0) return 1;
            let n = 0;
            for (let v = 1; v <= 9 && n < limit; v++) { if (this.ok(b, i, v)) { b[i] = v; n += this.count(b, limit - n); b[i] = 0; } }
            return n;
        },
        generate() {
            const full = Array(81).fill(0); this.fill(full);
            const p = [...full];
            const target = { easy: 38, medium: 47, hard: 53 }[this.level];
            let removed = 0;
            for (const i of BF.shuffle([...Array(81).keys()])) {
                if (removed >= target) break;
                const keep = p[i]; p[i] = 0;
                if (this.count([...p]) !== 1) p[i] = keep; else removed++;
            }
            this.solution = full; this.puzzle = p; this.board = [...p];
            this.sel = null; this.mistakes = 0; this.won = false; this.secs = 0;
            clearInterval(this.timer); const t0 = Date.now(); this.timer = setInterval(() => this.secs = Math.floor((Date.now() - t0) / 1000), 500);
            BF.played({ url: location.pathname, title: 'Sudoku', type: 'game' });
        },
        put(v) {
            if (this.sel === null || this.puzzle[this.sel] || this.won) return;
            this.board[this.sel] = v;
            if (v && v !== this.solution[this.sel]) this.mistakes++;
            if (this.board.every((x, i) => x === this.solution[i])) {
                this.won = true; clearInterval(this.timer); this.best = BF.best('sudoku_' + this.level, this.secs, true);
            }
        },
        same(i) { return this.sel !== null && (Math.floor(i / 9) === Math.floor(this.sel / 9) || i % 9 === this.sel % 9 || (Math.floor(i / 27) === Math.floor(this.sel / 27) && Math.floor(i % 9 / 3) === Math.floor(this.sel % 9 / 3))) },
        cls(i) {
            const v = this.board[i];
            let c = this.puzzle[i] ? 'font-extrabold text-slate-900 ' : 'font-semibold text-navy-600 ';
            if (this.check && v && !this.puzzle[i] && v !== this.solution[i]) c = 'font-semibold text-red-600 ';
            if (i === this.sel) return c + 'bg-brand-200';
            if (this.sel !== null && v && v === this.board[this.sel]) return c + 'bg-brand-100';
            return c + (this.same(i) ? 'bg-slate-100' : 'bg-white');
        },
        fmt(s) { return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0') },
     }" x-init="generate()"
     @keydown.window="if (/^[1-9]$/.test($event.key)) put(+$event.key); if ($event.key === 'Backspace' || $event.key === '0') put(0)">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ $game->title }}</h1>
    <div class="mt-3 flex flex-wrap items-center justify-center gap-2 text-sm">
        <template x-for="l in ['easy', 'medium', 'hard']" :key="l">
            <button type="button" @click="level = l; generate()" :class="level === l ? 'bg-navy-600 text-white' : 'bg-slate-100'" class="rounded-full px-3 py-1 font-semibold capitalize" x-text="l"></button>
        </template>
        <label class="ml-2 flex items-center gap-1 text-slate-600"><input type="checkbox" x-model="check"> Show mistakes</label>
    </div>
    <p class="mt-2 text-sm text-slate-600">⏱ <span x-text="fmt(secs)"></span> · Mistakes: <span x-text="mistakes"></span> · Best: <span x-text="BF.getBest('sudoku_' + level) !== null ? fmt(BF.getBest('sudoku_' + level)) : '—'"></span></p>
    <div class="mx-auto mt-3 grid aspect-square w-full max-w-sm grid-cols-9 border-2 border-slate-800 bg-slate-800">
        <template x-for="(v, i) in board" :key="i">
            <button type="button" @click="sel = i" :class="cls(i) + (i % 9 === 2 || i % 9 === 5 ? ' border-r-2 border-r-slate-800' : ' border-r border-r-slate-300') + (Math.floor(i / 9) === 2 || Math.floor(i / 9) === 5 ? ' border-b-2 border-b-slate-800' : ' border-b border-b-slate-300')"
                    class="flex aspect-square items-center justify-center text-lg sm:text-xl" x-text="v || ''" :aria-label="`Row ${Math.floor(i / 9) + 1} column ${i % 9 + 1}: ${v || 'empty'}`"></button>
        </template>
    </div>
    <div class="mx-auto mt-4 grid max-w-sm grid-cols-5 gap-2">
        <template x-for="n in [1,2,3,4,5,6,7,8,9]" :key="n">
            <button type="button" @click="put(n)" class="rounded-xl border border-slate-300 bg-white py-3 text-xl font-bold active:bg-brand-50" x-text="n"></button>
        </template>
        <button type="button" @click="put(0)" class="rounded-xl border border-slate-300 bg-white py-3 text-xl" aria-label="Erase">⌫</button>
    </div>
    <div x-show="won" x-cloak class="mt-5 rounded-2xl bg-emerald-50 p-5">
        <p class="text-xl font-bold text-emerald-800">Solved in <span x-text="fmt(secs)"></span>!</p>
        <p class="text-sm" x-text="best && best.isNew ? 'New best time!' : ''"></p>
        <div class="mt-3 flex justify-center gap-2">
            <button type="button" @click="generate()" class="rounded-full bg-brand-600 px-5 py-2 font-semibold text-white">New puzzle</button>
            <button type="button" @click="BF.share('I solved a ' + level + ' Sudoku in ' + fmt(secs) + ' on BuntyFun 🔢')" class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white">Share</button>
        </div>
    </div>
    <button type="button" x-show="!won" @click="generate()" class="mt-4 text-sm text-slate-500 underline">New puzzle</button>
</div>
@include('games.partials.guide')
</x-layout>
