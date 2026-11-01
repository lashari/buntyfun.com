@php
    $pool = [
        ['q' => 'What has keys but no locks, space but no room, and you can enter but not go in?', 'options' => ['A map', 'A keyboard', 'A piano', 'A door'], 'correct' => 1],
        ['q' => 'The more you take, the more you leave behind. What am I?', 'options' => ['Time', 'Footsteps', 'Money', 'Memories'], 'correct' => 1],
        ['q' => 'What month of the year has 28 days?', 'options' => ['February only', 'All of them', 'June', 'None'], 'correct' => 1],
        ['q' => 'I speak without a mouth and hear without ears. What am I?', 'options' => ['A ghost', 'An echo', 'A phone', 'A shadow'], 'correct' => 1],
        ['q' => 'What has a neck but no head?', 'options' => ['A bottle', 'A shirt', 'A guitar', 'A road'], 'correct' => 0],
        ['q' => 'What gets wetter the more it dries?', 'options' => ['A sponge', 'A towel', 'Rain', 'Soap'], 'correct' => 1],
        ['q' => 'What can you break without touching it?', 'options' => ['A promise', 'Glass', 'A record', 'Silence'], 'correct' => 0],
    ];
    $index = (int) date('z') % count($pool);
    $question = $pool[$index];
@endphp
<x-layout :title="$game->title.' — BuntyFun'" description="A new brain teaser every day — free browser game.">
    <div class="mx-auto max-w-md px-4 py-10 text-center"
         x-data="{ selected: null, correct: {{ $question['correct'] }} }">
        <h1 class="text-2xl font-bold">{{ $game->title }}</h1>
        <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ now()->format('F j, Y') }}</p>

        <p class="mt-6 text-lg font-medium">{{ $question['q'] }}</p>

        <div class="mt-6 grid gap-3">
            @foreach ($question['options'] as $i => $option)
                <button type="button" @click="selected === null && (selected = {{ $i }})"
                        :class="selected === null ? 'border-slate-200 hover:border-violet-300' : (
                            {{ $i }} === correct ? 'border-emerald-500 bg-emerald-50' : (selected === {{ $i }} ? 'border-red-400 bg-red-50' : 'border-slate-200 opacity-60')
                        )"
                        class="rounded-lg border px-4 py-3 text-left">
                    {{ $option }}
                </button>
            @endforeach
        </div>

        <p class="mt-4 font-semibold text-violet-600" x-show="selected !== null" x-cloak>
            <span x-text="selected === correct ? 'Correct! Come back tomorrow for a new one.' : 'Not quite — come back tomorrow for a new one.'"></span>
        </p>
        <a href="{{ route('games.index') }}" class="mt-6 block text-sm text-slate-500 hover:underline">More games</a>
    </div>
</x-layout>
