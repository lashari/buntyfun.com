{{-- Shared engine: $mode = 'flag' (pick the country for a flag) or 'capital' (pick the capital). --}}
@php $countries = \App\Content\Countries::forJs($mode === 'capital'); @endphp
<div class="mx-auto max-w-lg px-4 py-8 text-center"
     x-data="{
        all: {{ Js::from($countries) }}, mode: '{{ $mode }}', level: 'easy', rounds: 10,
        list: [], i: 0, options: [], picked: null, marks: [], started: false, done: false, t0: 0, secs: 0, timer: null, best: null, run: 0, bestRun: 0,
        continents: {{ Js::from(\App\Content\Countries::CONTINENTS) }},
        pool() {
            if (this.level === 'easy') return this.all.filter(c => c.e);
            if (this.level === 'all') return this.all;
            return this.all.filter(c => c.k === this.level);
        },
        start() {
            const p = this.pool();
            this.list = BF.shuffle(p).slice(0, Math.min(this.rounds, p.length));
            this.i = 0; this.marks = []; this.run = 0; this.bestRun = 0; this.done = false; this.started = true; this.picked = null;
            this.t0 = Date.now(); clearInterval(this.timer); this.timer = setInterval(() => this.secs = Math.floor((Date.now() - this.t0) / 1000), 250);
            this.makeOptions();
            BF.played({ url: location.pathname, title: document.title.split(' — ')[0], type: 'game' });
        },
        get cur() { return this.list[this.i] },
        label(c) { return this.mode === 'capital' ? c.cap : c.n },
        makeOptions() {
            const c = this.cur;
            // Wrong options from the same continent first: harder and more educational than random ones.
            const near = BF.shuffle(this.all.filter(x => x.c !== c.c && x.k === c.k && this.label(x) !== this.label(c)));
            const far = BF.shuffle(this.all.filter(x => x.c !== c.c && x.k !== c.k));
            this.options = BF.shuffle([c, ...[...near, ...far].slice(0, 3)]);
        },
        pick(o) {
            if (this.picked) return;
            this.picked = o;
            const ok = o.c === this.cur.c;
            this.marks.push(ok);
            this.run = ok ? this.run + 1 : 0; this.bestRun = Math.max(this.bestRun, this.run);
            setTimeout(() => this.next(), ok ? 700 : 1600);
        },
        next() {
            this.picked = null;
            if (this.i < this.list.length - 1) { this.i++; this.makeOptions(); return; }
            this.done = true; clearInterval(this.timer);
            this.best = BF.best('{{ $mode }}_' + this.level, this.score);
        },
        get score() { return this.marks.filter(Boolean).length },
        share() {
            BF.share('{{ $mode === 'capital' ? 'Capital Challenge' : 'Flag Challenge' }}: ' + this.score + '/' + this.list.length + ' in ' + this.secs + 's ' + this.marks.map(m => m ? '🟩' : '🟥').join('') + ' Can you beat me?');
        },
     }">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ $game->title }}</h1>
    <p class="mt-2 text-sm text-slate-600">{{ $mode === 'capital' ? 'Name the capital city of each country.' : 'Which country does this flag belong to?' }} {{ count($countries) }} countries.</p>

    <div x-show="!started" class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm">
        <p class="text-sm font-semibold">Choose a level</p>
        <div class="mt-3 flex flex-wrap gap-2">
            <button type="button" @click="level = 'easy'" :class="level === 'easy' ? 'bg-navy-600 text-white' : 'bg-slate-100'" class="rounded-full px-4 py-2 text-sm font-semibold">Easy (well-known)</button>
            <button type="button" @click="level = 'all'" :class="level === 'all' ? 'bg-navy-600 text-white' : 'bg-slate-100'" class="rounded-full px-4 py-2 text-sm font-semibold">All countries</button>
            <template x-for="(name, key) in continents" :key="key">
                <button type="button" @click="level = key" :class="level === key ? 'bg-navy-600 text-white' : 'bg-slate-100'" class="rounded-full px-4 py-2 text-sm font-semibold" x-text="name"></button>
            </template>
        </div>
        <p class="mt-3 text-xs text-slate-500">Best score at this level: <span x-text="BF.getBest('{{ $mode }}_' + level) ?? '—'"></span></p>
        <button type="button" @click="start()" class="mt-4 w-full rounded-full bg-brand-600 py-3 text-lg font-bold text-white hover:bg-brand-700">Play 10 rounds</button>
    </div>

    <template x-if="started && !done && cur"><div class="mt-6">
        <div class="flex justify-between text-sm font-semibold text-slate-600">
            <span>Round <span x-text="i + 1"></span>/<span x-text="list.length"></span></span>
            <span>⏱ <span x-text="secs"></span>s</span>
            <span>✔ <span x-text="score"></span> · 🔥 <span x-text="run"></span></span>
        </div>
        <div class="mt-4 flex min-h-40 items-center justify-center rounded-2xl bg-slate-50 p-4">
            @if ($mode === 'flag')
                <img :src="'/images/flags/' + cur.c + '.svg'" alt="Flag to guess" width="240" height="180" class="h-auto max-h-44 w-60 rounded border border-slate-200 bg-white shadow">
            @else
                <div>
                    <img :src="'/images/flags/' + cur.c + '.svg'" alt="" width="96" height="72" class="mx-auto h-auto w-20 rounded border border-slate-200 shadow-sm">
                    <p class="mt-3 text-sm text-slate-500">What is the capital of</p>
                    <p class="text-2xl font-bold" x-text="cur.n + '?'"></p>
                </div>
            @endif
        </div>
        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
            <template x-for="o in options" :key="o.c">
                <button type="button" @click="pick(o)" :disabled="picked !== null"
                        class="rounded-xl border-2 px-4 py-3 text-base font-semibold transition"
                        :class="!picked ? 'border-slate-200 bg-white hover:border-brand-400' : (o.c === cur.c ? 'border-emerald-500 bg-emerald-50 text-emerald-800' : (o.c === picked.c ? 'border-red-400 bg-red-50 text-red-700' : 'border-slate-200 opacity-50'))"
                        x-text="label(o)"></button>
            </template>
        </div>
        <p x-show="picked && picked.c !== cur.c" x-cloak class="mt-3 text-sm text-slate-600">
            {{ $mode === 'capital' ? 'The capital of' : 'This is the flag of' }} <strong x-text="cur.n"></strong>{{ $mode === 'capital' ? ' is ' : '.' }}<strong x-show="mode === 'capital'" x-text="cur.cap + '.'"></strong>
        </p>
    </div></template>

    <div x-show="done" x-cloak class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-5xl font-extrabold"><span x-text="score"></span>/<span x-text="list.length"></span></p>
        <p class="mt-2 text-2xl tracking-widest" x-text="marks.map(m => m ? '🟩' : '🟥').join('')"></p>
        <p class="mt-2 text-slate-600">Time <span x-text="secs"></span>s · best run <span x-text="bestRun"></span> in a row</p>
        <p class="mt-1 text-sm font-semibold" :class="best && best.isNew ? 'text-emerald-700' : 'text-slate-500'" x-text="best && best.isNew ? 'New personal best!' : 'Your best at this level: ' + (best ? best.best : '')"></p>
        <div class="mt-5 flex flex-wrap justify-center gap-2">
            <button type="button" @click="start()" class="rounded-full bg-brand-600 px-6 py-2 font-semibold text-white">Play again</button>
            <button type="button" @click="share()" class="rounded-full bg-emerald-700 px-6 py-2 font-semibold text-white">Share score</button>
            <button type="button" @click="started = false; done = false" class="rounded-full border border-slate-300 px-6 py-2 font-semibold">Change level</button>
        </div>
    </div>
    <p class="mt-6 text-xs text-slate-400">Flag images: <a href="https://github.com/lipis/flag-icons" class="underline" rel="noopener nofollow" target="_blank">flag-icons</a> (MIT).</p>
</div>
