@php
    $quiz->loadMissing('questions.answers');
    $picked = collect($result->answers_json ?? [])->mapWithKeys(fn ($a, $q) => [(int) $q => (int) $a]);
    $isTrivia = $quiz->questions->flatMap->answers->contains(fn ($a) => ! is_null($a->is_correct));
    $shareText = $quiz->title.': '.$result->outcome_label;
    $outcomes = [
        'Best Friends Forever' => 'Your answers matched closely. You clearly pay attention to the little things about each other.',
        'Good Friends' => 'You know each other well, with a few surprises left. Comparing answers is half the fun.',
        'Still Getting to Know Each Other' => 'Plenty still to discover, which is a good excuse to spend more time together.',
    ];
@endphp
<x-layout :title="$quiz->title.' — Result — BuntyFun'" description="See your quiz result." :noindex="true" :ads="false">
    <div class="mx-auto max-w-2xl px-4 py-12">
        <div class="text-center">
            <p class="text-sm font-medium text-violet-600">{{ $quiz->title }}</p>
            <h1 class="mt-2 text-3xl font-extrabold">{{ $result->outcome_label }}</h1>
            @isset($outcomes[$result->outcome_label])
                <p class="mt-3 text-slate-600">{{ $outcomes[$result->outcome_label] }}</p>
            @endisset

            <div class="mt-8 flex flex-wrap justify-center gap-3"
                 x-data="{ copied: false, copy() { navigator.clipboard.writeText(window.location.href); this.copied = true; setTimeout(() => this.copied = false, 2000); } }">
                <a :href="`https://wa.me/?text=${encodeURIComponent({{ Js::from($shareText) }} + ' ' + window.location.href)}`"
                   target="_blank" rel="noopener"
                   class="rounded-full bg-emerald-700 px-5 py-2 font-semibold text-white hover:bg-emerald-800">
                    Share on WhatsApp
                </a>
                <button type="button" @click="copy()" class="rounded-full border border-slate-300 px-5 py-2 font-semibold hover:bg-slate-50">
                    <span x-show="!copied">Copy link</span>
                    <span x-show="copied" x-cloak>Copied!</span>
                </button>
            </div>
        </div>

        @if ($isTrivia)
            <h2 class="mt-12 text-xl font-bold">Answers explained</h2>
            <ol class="mt-4 space-y-4">
                @foreach ($quiz->questions as $question)
                    @php
                        $right = $question->answers->firstWhere('is_correct', true);
                        $mine = $question->answers->firstWhere('id', $picked[$question->id] ?? 0);
                        $ok = $mine && $mine->is_correct;
                    @endphp
                    <li class="rounded-xl border {{ $ok ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-white' }} p-5">
                        <p class="font-semibold">{{ $loop->iteration }}. {{ $question->prompt }}</p>
                        <p class="mt-2 text-sm">
                            @if ($mine)
                                Your answer: <span class="{{ $ok ? 'text-emerald-700' : 'text-red-600' }} font-medium">{{ $mine->label }}</span> {{ $ok ? '✓' : '✗' }}
                            @endif
                            @if ($right && ! $ok)
                                <span class="block">Correct answer: <span class="font-medium text-emerald-700">{{ $right->label }}</span></span>
                            @endif
                        </p>
                        @if ($question->explanation)
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $question->explanation }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        @endif

        <div class="mt-10 text-center">
            <a href="{{ route('quizzes.show', $quiz) }}" class="block text-sm text-slate-500 hover:underline">Retake this quiz</a>
            <a href="{{ route('quizzes.index') }}" class="mt-2 block text-sm text-slate-500 hover:underline">More quizzes</a>
        </div>
    </div>
</x-layout>
