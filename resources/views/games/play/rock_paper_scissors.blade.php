<x-layout :title="$game->title.' — BuntyFun'" description="Play Rock Paper Scissors free in your browser.">
    <div class="mx-auto max-w-sm px-4 py-10 text-center"
         x-data="{
             choices: ['rock', 'paper', 'scissors'],
             emoji: { rock: '✊', paper: '✋', scissors: '✌️' },
             you: null,
             cpu: null,
             result: null,
             score: { wins: 0, losses: 0, draws: 0 },
             play(choice) {
                 this.you = choice;
                 this.cpu = this.choices[Math.floor(Math.random() * 3)];
                 if (this.you === this.cpu) { this.result = 'draw'; this.score.draws++; }
                 else if (
                     (this.you === 'rock' && this.cpu === 'scissors') ||
                     (this.you === 'paper' && this.cpu === 'rock') ||
                     (this.you === 'scissors' && this.cpu === 'paper')
                 ) { this.result = 'win'; this.score.wins++; }
                 else { this.result = 'lose'; this.score.losses++; }
             },
         }">
        <h1 class="text-2xl font-bold">{{ $game->title }}</h1>
        <p class="mt-2 text-sm text-slate-500">Wins: <span x-text="score.wins"></span> · Losses: <span x-text="score.losses"></span> · Draws: <span x-text="score.draws"></span></p>

        <div class="mt-6 flex justify-center gap-4 text-5xl" x-show="you" x-cloak>
            <span x-text="emoji[you]"></span>
            <span class="text-2xl text-slate-500 self-center">vs</span>
            <span x-text="emoji[cpu]"></span>
        </div>
        <p class="mt-4 font-semibold"
           :class="{ 'text-emerald-600': result === 'win', 'text-red-600': result === 'lose', 'text-slate-500': result === 'draw' }"
           x-show="result" x-cloak>
            <span x-text="result === 'win' ? 'You win!' : result === 'lose' ? 'You lose!' : 'Draw!'"></span>
        </p>

        <div class="mt-8 flex justify-center gap-3">
            <template x-for="choice in choices" :key="choice">
                <button type="button" @click="play(choice)" :aria-label="choice"
                        class="rounded-full border border-slate-200 bg-white px-5 py-3 text-2xl hover:bg-slate-50">
                    <span x-text="emoji[choice]" aria-hidden="true"></span>
                </button>
            </template>
        </div>
        <a href="{{ route('games.index') }}" class="mt-8 block text-sm text-slate-500 hover:underline">More games</a>
    </div>
</x-layout>
