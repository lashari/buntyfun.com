<x-layout :title="$game->title.' — Play Free Online, Rules & Tips | BuntyFun'" :breadcrumbs="['Games' => route('games.index'), $game->title => null]" :description="(\App\Content\GameGuides::for($game->slug)['summary'] ?? 'Unscramble the word — free browser word game.').' Free, no sign-up, with rules and tips.'">
    <div class="mx-auto max-w-md px-4 py-10 text-center"
         x-data="{
             words: ['puzzle', 'sunshine', 'birthday', 'friend', 'wisdom', 'journey', 'rainbow', 'victory', 'melody', 'garden', 'planet', 'thunder', 'blanket', 'kitchen', 'holiday', 'monsoon', 'mango', 'cricket', 'library', 'picnic'],
             order: [],
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
                 // Shuffle once per game so the five rounds never repeat a word.
                 if (this.round === 0) this.order = [...this.words].sort(() => Math.random() - 0.5);
                 this.word = this.order[this.round % this.order.length];
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

        <div class="mt-8 text-4xl font-bold uppercase tracking-widest text-navy-600" x-text="scrambled" x-show="round < totalRounds"></div>

        <form @submit.prevent="submitGuess()" class="mt-6" x-show="round < totalRounds">
            <input type="text" x-model="guess" autocomplete="off"
                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-center focus:border-brand-500 focus:outline-none"
                   placeholder="Your guess">
            <button type="submit" class="mt-3 rounded-full bg-brand-600 px-6 py-2 font-semibold text-white hover:bg-brand-700">Submit</button>
        </form>

        <p class="mt-4 font-medium text-slate-600" x-text="message" x-cloak></p>

        <button type="button" @click="round = 0; score = 0; start()" class="mt-6 text-sm text-slate-500 hover:underline">Play again</button>
        <a href="{{ route('games.index') }}" class="mt-2 block text-sm text-slate-500 hover:underline">More games</a>
    </div>
    @include('games.partials.guide')
</x-layout>
