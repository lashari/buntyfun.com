<x-layout :title="$game->title.' — BuntyFun'" description="Play Tic-Tac-Toe free in your browser.">
    <div class="mx-auto max-w-sm px-4 py-10 text-center"
         x-data="{
             board: Array(9).fill(null),
             turn: 'X',
             winner: null,
             lines: [[0,1,2],[3,4,5],[6,7,8],[0,3,6],[1,4,7],[2,5,8],[0,4,8],[2,4,6]],
             play(i) {
                 if (this.board[i] || this.winner) return;
                 this.board[i] = this.turn;
                 this.checkWinner();
                 this.turn = this.turn === 'X' ? 'O' : 'X';
             },
             checkWinner() {
                 for (const line of this.lines) {
                     const [a, b, c] = line;
                     if (this.board[a] && this.board[a] === this.board[b] && this.board[a] === this.board[c]) {
                         this.winner = this.board[a];
                     }
                 }
                 if (!this.winner && !this.board.includes(null)) this.winner = 'draw';
             },
             reset() { this.board = Array(9).fill(null); this.turn = 'X'; this.winner = null; },
         }">
        <h1 class="text-2xl font-bold">{{ $game->title }}</h1>
        <p class="mt-2 text-sm text-slate-500" x-show="!winner">Turn: <span x-text="turn"></span></p>
        <p class="mt-2 font-semibold text-violet-600" x-show="winner === 'draw'" x-cloak>It's a draw!</p>
        <p class="mt-2 font-semibold text-violet-600" x-show="winner && winner !== 'draw'" x-cloak>
            <span x-text="winner"></span> wins!
        </p>

        <div class="mx-auto mt-6 grid w-64 grid-cols-3 gap-2">
            <template x-for="(cell, i) in board" :key="i">
                <button type="button" @click="play(i)"
                        :aria-label="cell ? `Cell ${i + 1}: ${cell}` : `Cell ${i + 1}, empty`"
                        class="flex h-20 w-20 items-center justify-center rounded-lg border border-slate-200 bg-white text-3xl font-bold hover:bg-slate-50">
                    <span x-text="cell"></span>
                </button>
            </template>
        </div>

        <button type="button" @click="reset()" class="mt-6 rounded-full bg-violet-600 px-6 py-2 font-semibold text-white hover:bg-violet-700">
            New Game
        </button>
        <a href="{{ route('games.index') }}" class="mt-4 block text-sm text-slate-500 hover:underline">More games</a>
    </div>
</x-layout>
