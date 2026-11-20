@php
    $pool = [
        ['q' => 'What has keys but no locks, space but no room, and you can enter but not go in?', 'options' => ['A map', 'A keyboard', 'A piano', 'A door'], 'correct' => 1,
         'why' => 'A keyboard has keys, a space bar and an Enter key, but none of them open anything.'],
        ['q' => 'The more you take, the more you leave behind. What am I?', 'options' => ['Time', 'Footsteps', 'Money', 'Memories'], 'correct' => 1,
         'why' => 'Every step you take leaves another footprint behind you.'],
        ['q' => 'What month of the year has 28 days?', 'options' => ['February only', 'All of them', 'June', 'None'], 'correct' => 1,
         'why' => 'Every month has at least 28 days. February is just the only one that sometimes stops there.'],
        ['q' => 'I speak without a mouth and hear without ears. What am I?', 'options' => ['A ghost', 'An echo', 'A phone', 'A shadow'], 'correct' => 1,
         'why' => 'An echo "repeats" what it hears, but it is only sound bouncing back from a surface.'],
        ['q' => 'What has a neck but no head?', 'options' => ['A bottle', 'A shirt', 'A guitar', 'A road'], 'correct' => 0,
         'why' => 'The narrow top part of a bottle is called its neck. (A guitar has a neck too, but it also has a "head" at the end, called the headstock.)'],
        ['q' => 'What gets wetter the more it dries?', 'options' => ['A sponge', 'A towel', 'Rain', 'Soap'], 'correct' => 1,
         'why' => 'A towel takes the water off whatever it dries, so it gets wetter itself.'],
        ['q' => 'What can you break without touching it?', 'options' => ['A promise', 'Glass', 'A record', 'Silence'], 'correct' => 0,
         'why' => 'You break a promise just by not keeping it. (You can break silence too, but only a promise can be broken without doing anything at all.)'],
        ['q' => 'What has hands but cannot clap?', 'options' => ['A statue', 'A clock', 'A glove', 'A robot'], 'correct' => 1,
         'why' => 'A clock has an hour hand and a minute hand, but they only point.'],
        ['q' => 'What goes up but never comes down?', 'options' => ['A balloon', 'Your age', 'Smoke', 'A kite'], 'correct' => 1,
         'why' => 'Your age only ever increases. Balloons, smoke and kites can all come back down.'],
        ['q' => 'What has many teeth but cannot bite?', 'options' => ['A comb', 'A shark', 'A dog', 'A crocodile'], 'correct' => 0,
         'why' => 'A comb has a row of "teeth" for your hair, but it cannot bite. The animals all can.'],
        ['q' => 'What is full of holes but still holds water?', 'options' => ['A net', 'A sponge', 'A bucket', 'A sieve'], 'correct' => 1,
         'why' => 'A sponge is full of tiny holes, and that is exactly what lets it soak up and hold water. A net and a sieve let water through.'],
        ['q' => 'What can travel around the world while staying in a corner?', 'options' => ['A stamp', 'A plane', 'The wind', 'A satellite'], 'correct' => 0,
         'why' => 'A postage stamp sits in the corner of an envelope while the letter travels anywhere.'],
        ['q' => 'If you have me, you want to share me. If you share me, you no longer have me. What am I?', 'options' => ['Money', 'A secret', 'Food', 'Advice'], 'correct' => 1,
         'why' => 'Once you tell a secret, it is not a secret any more.'],
        ['q' => 'What has one eye but cannot see?', 'options' => ['A needle', 'A pencil', 'A spoon', 'A camera'], 'correct' => 0,
         'why' => 'The hole you thread at the top of a needle is called its eye. A camera "sees" through its lens, so it does not fit.'],
    ];
    $index = (int) date('z') % count($pool);
    $question = $pool[$index];
@endphp
<x-layout :title="$game->title.' — Play Free Online, Rules & Tips | BuntyFun'" :breadcrumbs="['Games' => route('games.index'), $game->title => null]" :description="(\App\Content\GameGuides::for($game->slug)['summary'] ?? 'A new brain teaser every day — free browser game.').' Free, no sign-up, with rules and tips.'">
    <div class="mx-auto max-w-md px-4 py-10 text-center"
         x-data="{ selected: null, correct: {{ $question['correct'] }} }">
        <h1 class="text-2xl font-bold">{{ $game->title }}</h1>
        <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ now()->format('F j, Y') }}</p>

        <p class="mt-6 text-lg font-medium">{{ $question['q'] }}</p>

        <div class="mt-6 grid gap-3">
            @foreach ($question['options'] as $i => $option)
                <button type="button" @click="selected === null && (selected = {{ $i }})"
                        :class="selected === null ? 'border-slate-200 hover:border-brand-300' : (
                            {{ $i }} === correct ? 'border-emerald-500 bg-emerald-50' : (selected === {{ $i }} ? 'border-red-400 bg-red-50' : 'border-slate-200 opacity-60')
                        )"
                        class="rounded-lg border px-4 py-3 text-left">
                    {{ $option }}
                </button>
            @endforeach
        </div>

        <p class="mt-4 font-semibold text-navy-600" x-show="selected !== null" x-cloak>
            <span x-text="selected === correct ? 'Correct! Come back tomorrow for a new one.' : 'Not quite — come back tomorrow for a new one.'"></span>
        </p>
        <p class="mt-2 text-sm text-slate-600" x-show="selected !== null" x-cloak>{{ $question['why'] }}</p>
        <a href="{{ route('games.index') }}" class="mt-6 block text-sm text-slate-500 hover:underline">More games</a>
    </div>
    @include('games.partials.guide')
</x-layout>
