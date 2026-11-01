<x-layout :title="$game->title.' — BuntyFun'" description="Unscramble the word — free browser word game.">
    <div class="mx-auto max-w-md px-4 py-10 text-center"
         x-data="{
             words: ['laravel', 'puzzle', 'sunshine', 'birthday', 'friend', 'wisdom', 'journey', 'rainbow', 'victory', 'melody'],
             round: 0,
             totalRounds: 5,
             word: '',
             scrambled: '',
             guess: '',
             score: 0,
             message: '',
             scramble(w) {
                 let arr = w.split('');
                 do { arr = arr.sort(() => Math.random() - 0.5); } while (arr.join('') === w);
                 return arr.join('');
             },
             start() {
                 const pool = [...this.words].sort(() => Math.random() - 0.5);
                 this.word = pool[this.round % pool.length];
                 this.scrambled = this.scramble(this.word);
                 this.guess = '';
                 this.message = '';
             },
             submitGuess() {
                 if (this.guess.trim().toLowerCase() === this.word) {
                     this.score++;
                     this.message = 'Correct!';
                 } else {
                     this.message = `Nope — it was \"${this.word}\"`;
                 }
                 this.round++;
                 if (this.round < this.totalRounds) {
                     setTimeout(() => this.start(), 900);
                 } else {
                     setTimeout(() => { this.message = `Final score: ${this.score}/${this.totalRounds}`; }, 900);
                 }
             },
         }" x-init="start()">
        <h1 class="text-2xl font-bold">{{ $game->title }}</h1>
        <p class="mt-2 text-sm text-slate-500" x-text="`Round ${Math.min(round + 1, totalRounds)} of ${totalRounds} · Score: ${score}`"></p>

        <div class="mt-8 text-4xl font-bold uppercase tracking-widest text-violet-700" x-text="scrambled" x-show="round < totalRounds"></div>

        <form @submit.prevent="submitGuess()" class="mt-6" x-show="round < totalRounds">
            <input type="text" x-model="guess" autocomplete="off"
                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-center focus:border-violet-500 focus:outline-none"
                   placeholder="Your guess">
            <button type="submit" class="mt-3 rounded-full bg-violet-600 px-6 py-2 font-semibold text-white hover:bg-violet-700">Submit</button>
        </form>

        <p class="mt-4 font-medium text-slate-600" x-text="message" x-cloak></p>

        <button type="button" @click="round = 0; score = 0; start()" class="mt-6 text-sm text-slate-500 hover:underline">Play again</button>
        <a href="{{ route('games.index') }}" class="mt-2 block text-sm text-slate-500 hover:underline">More games</a>
    </div>
</x-layout>
