<x-layout :title="$game->title.' — Play Free Online, Rules & Tips | BuntyFun'" :breadcrumbs="['Games' => route('games.index'), $game->title => null]" :description="\App\Content\GameGuides::for($game->slug)['summary'].' Free, no sign-up, works on phones.'">
<div class="mx-auto max-w-sm px-4 py-8 text-center"
     x-data="{
        level: 'easy', state: 'menu', left: 60, score: 0, wrong: 0, q: null, input: '', flash: '', timer: null, best: null, combo: 0,
        make() {
            const R = (a, b) => a + Math.floor(Math.random() * (b - a + 1));
            const L = { easy: { ops: ['+', '-'], max: 20 }, medium: { ops: ['+', '-', '×'], max: 50 }, hard: { ops: ['+', '-', '×', '÷'], max: 100 } }[this.level];
            const op = L.ops[R(0, L.ops.length - 1)];
            let a, b, ans;
            if (op === '+') { a = R(1, L.max); b = R(1, L.max); ans = a + b; }
            if (op === '-') { a = R(1, L.max); b = R(1, a); ans = a - b; }
            if (op === '×') { a = R(2, this.level === 'hard' ? 15 : 10); b = R(2, 12); ans = a * b; }
            if (op === '÷') { b = R(2, 12); ans = R(2, 12); a = b * ans; }
            this.q = { text: `${a} ${op} ${b}`, ans };
            this.input = '';
        },
        go() {
            this.state = 'play'; this.left = 60; this.score = 0; this.wrong = 0; this.combo = 0; this.make();
            clearInterval(this.timer);
            const end = Date.now() + 60000;
            this.timer = setInterval(() => { this.left = Math.max(0, Math.ceil((end - Date.now()) / 1000)); if (!this.left) this.finish(); }, 200);
            BF.played({ url: location.pathname, title: 'Maths Race', type: 'game' });
        },
        key(k) {
            if (this.state !== 'play') return;
            if (k === 'del') { this.input = this.input.slice(0, -1); return; }
            if (k === 'ok') { this.check(); return; }
            if (this.input.length < 4) this.input += k;
            if (this.input.length >= String(this.q.ans).length) this.check();
        },
        check() {
            if (this.input === '') return;
            if (Number(this.input) === this.q.ans) { this.score++; this.combo++; this.flash = 'ok'; this.make(); }
            else if (this.input.length >= String(this.q.ans).length) { this.wrong++; this.combo = 0; this.flash = 'no'; this.input = ''; }
            setTimeout(() => this.flash = '', 250);
        },
        finish() { clearInterval(this.timer); this.state = 'over'; this.best = BF.best('maths_' + this.level, this.score); },
     }" @keydown.window="if (state === 'play') { if (/^[0-9]$/.test($event.key)) key($event.key); if ($event.key === 'Backspace') key('del'); if ($event.key === 'Enter') key('ok'); }">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ $game->title }}</h1>
    <div x-show="state === 'menu'" class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-slate-600">Answer as many sums as you can in 60 seconds.</p>
        <div class="mt-4 grid grid-cols-3 gap-2">
            <button type="button" @click="level = 'easy'" :class="level === 'easy' ? 'bg-navy-600 text-white' : 'bg-slate-100'" class="rounded-xl py-2 font-semibold">Easy<span class="block text-xs font-normal">+ −</span></button>
            <button type="button" @click="level = 'medium'" :class="level === 'medium' ? 'bg-navy-600 text-white' : 'bg-slate-100'" class="rounded-xl py-2 font-semibold">Medium<span class="block text-xs font-normal">+ − ×</span></button>
            <button type="button" @click="level = 'hard'" :class="level === 'hard' ? 'bg-navy-600 text-white' : 'bg-slate-100'" class="rounded-xl py-2 font-semibold">Hard<span class="block text-xs font-normal">+ − × ÷</span></button>
        </div>
        <p class="mt-3 text-xs text-slate-500">Best at this level: <span x-text="BF.getBest('maths_' + level) ?? '—'"></span></p>
        <button type="button" @click="go()" class="mt-4 w-full rounded-full bg-brand-600 py-3 text-lg font-bold text-white">Start</button>
    </div>
    <template x-if="state === 'play'">
        <div class="mt-5">
            <div class="flex justify-between text-sm font-semibold"><span>⏱ <span x-text="left"></span>s</span><span>✔ <span x-text="score"></span></span><span>🔥 <span x-text="combo"></span></span></div>
            <div class="mt-2 h-2 rounded-full bg-slate-200"><div class="h-2 rounded-full bg-brand-500 transition-all" :style="`width:${left / 60 * 100}%`"></div></div>
            <div class="mt-5 rounded-2xl p-6 transition" :class="flash === 'ok' ? 'bg-emerald-100' : (flash === 'no' ? 'bg-red-100' : 'bg-slate-50')">
                <p class="text-4xl font-extrabold" x-text="q.text + ' ='"></p>
                <p class="mt-2 h-12 text-4xl font-bold text-navy-600" x-text="input || '?'"></p>
            </div>
            <div class="mt-4 grid grid-cols-3 gap-2">
                <template x-for="k in ['1','2','3','4','5','6','7','8','9','del','0','ok']" :key="k">
                    <button type="button" @click="key(k)" class="rounded-xl border border-slate-200 bg-white py-4 text-2xl font-bold active:bg-brand-50" x-text="k === 'del' ? '⌫' : (k === 'ok' ? '✓' : k)"></button>
                </template>
            </div>
        </div>
    </template>
    <div x-show="state === 'over'" x-cloak class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-5xl font-extrabold" x-text="score"></p>
        <p class="text-slate-600">correct answers in 60 seconds (<span x-text="wrong"></span> wrong)</p>
        <p class="mt-1 text-sm font-semibold" x-text="best && best.isNew ? 'New personal best!' : 'Your best: ' + (best ? best.best : '')"></p>
        <div class="mt-4 flex flex-wrap justify-center gap-2">
            <button type="button" @click="go()" class="rounded-full bg-brand-600 px-6 py-2 font-semibold text-white">Play again</button>
            <button type="button" @click="BF.share('I solved ' + score + ' ' + level + ' sums in 60 seconds on BuntyFun Maths Race ➗ Beat that!')" class="rounded-full bg-emerald-700 px-6 py-2 font-semibold text-white">Share</button>
            <button type="button" @click="state = 'menu'" class="rounded-full border border-slate-300 px-6 py-2 font-semibold">Level</button>
        </div>
    </div>
</div>
@include('games.partials.guide')
</x-layout>
