@php
    $quiz->loadMissing('questions.answers');
    $picked = collect($result->answers_json ?? [])->mapWithKeys(fn ($a, $q) => [(int) $q => (int) $a]);
    $isTrivia = $quiz->isTrivia();
    $shareText = $quiz->title.': '.$result->outcome_label;
    if ($isTrivia) {
        $total = max(1, $quiz->questions->count());
        $correct = $quiz->questions->filter(fn ($q) => $q->answers->firstWhere('id', $picked[$q->id] ?? 0)?->is_correct)->count();
        $band = $quiz->outcomeFor($correct / $total);
        $outcomeText = $band ? $band['label'].'. '.$band['text'] : null;
    } else {
        $outcomeText = $quiz->outcomeText($result->outcome_label) ?? [
            'Best Friends Forever' => 'Your answers matched closely. You clearly pay attention to the little things about each other.',
            'Good Friends' => 'You know each other well, with a few surprises left. Comparing answers is half the fun.',
            'Still Getting to Know Each Other' => 'Plenty still to discover, which is a good excuse to spend more time together.',
        ][$result->outcome_label] ?? null;
    }
    $more = \App\Models\Quiz::where('status', 'published')->whereKeyNot($quiz->id)->has('questions', '>=', 5)->inRandomOrder()->limit(3)->get();
@endphp
<x-layout :title="$quiz->title.' — Result — BuntyFun'" description="See your quiz result." :noindex="true" :ads="false">
    <div class="mx-auto max-w-2xl px-4 py-12">
        <div class="text-center">
            <p class="text-sm font-medium text-navy-600">{{ $quiz->title }}</p>
            <h1 class="mt-2 text-3xl font-extrabold">{{ $result->outcome_label }}</h1>
            @if ($outcomeText)
                <p class="mx-auto mt-3 max-w-xl leading-relaxed text-slate-600">{{ $outcomeText }}</p>
            @endif

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

        @include('quizzes.partials.sources')

        @if ($more->isNotEmpty())
            <h2 class="mt-12 text-xl font-bold">Try another quiz</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                @foreach ($more as $next)
                    <a href="{{ route('quizzes.show', $next) }}" class="rounded-xl border border-slate-200 bg-white p-4 text-sm font-semibold shadow-sm hover:shadow-md">{{ $next->title }}</a>
                @endforeach
            </div>
        @endif

        <div class="mt-10 text-center">
            <a href="{{ route('quizzes.show', $quiz) }}" class="block text-sm text-slate-500 hover:underline">Retake this quiz</a>
            <a href="{{ route('quizzes.index') }}" class="mt-2 block text-sm text-slate-500 hover:underline">All quizzes</a>
        </div>
    </div>
</x-layout>
