{{-- Five questions, instant feedback with explanations, daily streak and a shareable emoji grid. --}}
@php
    $data = $trivia->map(fn ($q) => [
        'q' => $q->prompt,
        'a' => collect($q->answerPairs())->map(fn ($p) => $p[0])->all(),
        'c' => collect($q->answerPairs())->search(fn ($p) => $p[1]),
        'why' => $q->explanation,
    ])->values();
@endphp
@if ($data->count() >= 3)
<div x-data="{
        qs: {{ Js::from($data) }}, i: 0, picked: null, marks: [], done: false, streak: 0, already: false, best: null,
        init() { this.streak = BF.streak('daily_trivia'); this.already = BF.doneToday('daily_trivia'); },
        get q() { return this.qs[this.i] },
        get score() { return this.marks.filter(Boolean).length },
        pick(j) { if (this.picked !== null) return; this.picked = j; this.marks.push(j === this.q.c); },
        next() {
            if (this.i < this.qs.length - 1) { this.i++; this.picked = null; return; }
            this.done = true;
            this.streak = BF.markDay('daily_trivia');
            this.best = BF.best('daily_trivia_' + BF.today(), this.score);
        },
        grid() { return this.marks.map(m => m ? '🟩' : '🟥').join('') },
        share() { BF.share('BuntyFun Daily Trivia {{ now()->format('M j') }}: ' + this.score + '/' + this.qs.length + ' ' + this.grid() + ' 🔥' + this.streak + ' day streak', {{ Js::from(route('daily')) }}) },
     }" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
    <div class="flex items-center justify-between text-sm">
        <span class="font-semibold text-navy-600">Question <span x-text="Math.min(i + 1, qs.length)"></span> of <span x-text="qs.length"></span></span>
        <span class="rounded-full bg-brand-50 px-3 py-1 font-semibold text-brand-700" title="Days in a row">🔥 <span x-text="streak"></span> day streak</span>
    </div>
    <div class="mt-2 flex gap-1" aria-hidden="true">
        <template x-for="(q, k) in qs" :key="k">
            <span class="h-1.5 flex-1 rounded-full" :class="k < marks.length ? (marks[k] ? 'bg-emerald-500' : 'bg-red-400') : (k === i ? 'bg-brand-400' : 'bg-slate-200')"></span>
        </template>
    </div>

    <div x-show="!done">
        <h3 class="mt-5 text-lg font-semibold leading-snug" x-text="q.q"></h3>
        <div class="mt-4 grid gap-2">
            <template x-for="(a, j) in q.a" :key="i + '-' + j">
                <button type="button" @click="pick(j)" :disabled="picked !== null"
                        class="rounded-xl border px-4 py-3 text-left font-medium transition"
                        :class="picked === null ? 'border-slate-200 hover:border-brand-400 hover:bg-brand-50' : (j === q.c ? 'border-emerald-500 bg-emerald-50 text-emerald-800' : (j === picked ? 'border-red-400 bg-red-50 text-red-700' : 'border-slate-200 opacity-60'))">
                    <span x-text="a"></span>
                </button>
            </template>
        </div>
        <div x-show="picked !== null" x-cloak class="mt-4 rounded-xl bg-slate-50 p-4 text-sm leading-relaxed text-slate-700">
            <p class="font-semibold" :class="picked === q.c ? 'text-emerald-700' : 'text-red-600'" x-text="picked === q.c ? 'Correct!' : 'Not quite.'"></p>
            <p class="mt-1" x-text="q.why"></p>
            <button type="button" @click="next()" class="mt-3 rounded-full bg-brand-600 px-5 py-2 font-semibold text-white hover:bg-brand-700" x-text="i < qs.length - 1 ? 'Next question' : 'See my score'"></button>
        </div>
    </div>

    <div x-show="done" x-cloak class="py-4 text-center">
        <p class="text-4xl font-extrabold"><span x-text="score"></span>/<span x-text="qs.length"></span></p>
        <p class="mt-2 text-2xl tracking-widest" x-text="grid()"></p>
        <p class="mt-2 text-slate-600" x-text="score === qs.length ? 'Perfect! A full house today.' : (score >= qs.length - 1 ? 'Great score!' : 'Good effort. New questions tomorrow.')"></p>
        <p class="mt-1 text-sm font-semibold text-brand-700">🔥 <span x-text="streak"></span>-day streak. Come back tomorrow to keep it going.</p>
        <div class="mt-4 flex flex-wrap justify-center gap-2">
            <button type="button" @click="share()" class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white">Share my score</button>
            <a href="{{ route('quizzes.index') }}" class="rounded-full border border-slate-300 px-5 py-2 font-semibold">More quizzes</a>
        </div>
    </div>
    <p class="mt-4 text-xs text-slate-500">Everyone gets the same five questions today. Your streak is saved only in this browser.</p>
</div>
@else
    <p class="rounded-xl bg-slate-50 p-5 text-sm text-slate-600">Daily Trivia starts as soon as the first reviewed questions are approved. Meanwhile, try our <a href="{{ route('quizzes.index') }}" class="underline">quizzes</a>.</p>
@endif
