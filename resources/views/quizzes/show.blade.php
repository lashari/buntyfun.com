@php $isTrivia = $quiz->questions->flatMap->answers->contains(fn ($a) => ! is_null($a->is_correct)); @endphp
<x-layout :title="$quiz->title.' — Free Online Quiz | BuntyFun'" :description="$quiz->intro ? Str::limit($quiz->intro, 150) : 'Take the '.$quiz->title.' — '.$quiz->questions->count().' questions, free, no sign-up.'"
          :meta="$quiz->meta" :noindex="$quiz->questions->count() < 5" :breadcrumbs="['Quizzes' => route('quizzes.index'), $quiz->title => null]">
    <div class="mx-auto max-w-2xl px-4 py-10">
        <h1 class="text-2xl font-bold">{{ $quiz->title }}</h1>
        @if ($quiz->intro)
            <p class="mt-3 leading-relaxed text-slate-700">{{ $quiz->intro }}</p>
        @endif
        <p class="mt-3 text-sm text-slate-500">
            {{ $quiz->questions->count() }} questions · one answer each · @if ($isTrivia) your score and an explanation for every answer at the end @else for fun, not a scientific test @endif
        </p>

        <form method="POST" action="{{ route('quizzes.submit', $quiz) }}"
              x-data="{
                  step: 0,
                  total: {{ $quiz->questions->count() }},
                  answers: {},
                  select(qId, aId) {
                      this.answers[qId] = aId;
                      if (this.step < this.total - 1) {
                          setTimeout(() => this.step++, 150);
                      }
                  },
              }" class="mt-6">
            @csrf

            <div class="h-2 w-full rounded-full bg-slate-200">
                <div class="h-2 rounded-full bg-violet-600 transition-all" :style="`width: ${((step + 1) / total) * 100}%`"></div>
            </div>
            <p class="mt-2 text-sm text-slate-500" x-text="`Question ${step + 1} of ${total}`"></p>

            @foreach ($quiz->questions as $index => $question)
                <div x-show="step === {{ $index }}" x-cloak class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">{{ $question->prompt }}</h2>
                    <div class="mt-4 grid gap-3">
                        @foreach ($question->answers as $answer)
                            <button type="button"
                                    @click="select({{ $question->id }}, {{ $answer->id }})"
                                    :class="answers[{{ $question->id }}] === {{ $answer->id }} ? 'border-violet-600 bg-violet-50' : 'border-slate-200 hover:border-violet-300'"
                                    class="rounded-lg border px-4 py-3 text-left">
                                {{ $answer->label }}
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" :name="`answers[{{ $question->id }}]`" :value="answers[{{ $question->id }}] || ''">
                </div>
            @endforeach

            <div class="mt-6 flex items-center justify-between">
                <button type="button" x-show="step > 0" @click="step--" x-cloak class="text-sm text-slate-500 hover:underline">Back</button>
                <button type="submit" x-show="step === total - 1 && answers[{{ $quiz->questions->last()?->id }}]" x-cloak
                        class="rounded-full bg-violet-600 px-6 py-2 font-semibold text-white hover:bg-violet-700">
                    See Results
                </button>
            </div>
        </form>
    </div>
</x-layout>
