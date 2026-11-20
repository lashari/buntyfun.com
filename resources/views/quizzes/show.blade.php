@php
    $isTrivia = $quiz->isTrivia();
    $count = $quiz->questions->count();
    $minutes = max(1, (int) ceil($count * 20 / 60));
@endphp
<x-layout :title="$quiz->title.' | BuntyFun'" :description="$quiz->intro ? Str::limit($quiz->intro, 150) : 'Take the '.$quiz->title.' — '.$quiz->questions->count().' questions, free, no sign-up.'"
          :meta="$quiz->meta" :noindex="$quiz->questions->count() < 5" :breadcrumbs="['Quizzes' => route('quizzes.index'), $quiz->title => null]">
    <div class="mx-auto max-w-2xl px-4 py-10">
        <h1 class="text-2xl font-bold">{{ $quiz->title }}</h1>
        @if ($quiz->intro)
            <p class="mt-3 leading-relaxed text-slate-700">{{ $quiz->intro }}</p>
        @endif
        <ul class="mt-4 flex flex-wrap gap-2 text-xs font-medium">
            <li class="rounded-full bg-brand-50 px-3 py-1 text-navy-600">{{ $count }} questions</li>
            <li class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">About {{ $minutes }} min</li>
            @if ($quiz->difficulty)<li class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ \App\Models\Quiz::DIFFICULTIES[$quiz->difficulty] ?? ucfirst($quiz->difficulty) }}</li>@endif
            <li class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ $isTrivia ? 'Score + every answer explained' : 'Just for fun, not a scientific test' }}</li>
        </ul>
        @if ($quiz->reviewed_at)
            <p class="mt-2 text-xs text-slate-500">{{ $isTrivia ? 'Facts checked' : 'Last reviewed' }} {{ $quiz->reviewed_at->format('M j, Y') }} · <a href="{{ route('pages.editorial') }}" class="underline">How we check quizzes</a></p>
        @endif


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
                <div class="h-2 rounded-full bg-brand-600 transition-all" :style="`width: ${((step + 1) / total) * 100}%`"></div>
            </div>
            <p class="mt-2 text-sm text-slate-500" x-text="`Question ${step + 1} of ${total}`"></p>

            @foreach ($quiz->questions as $index => $question)
                <div x-show="step === {{ $index }}" x-cloak class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">{{ $question->prompt }}</h2>
                    <div class="mt-4 grid gap-3">
                        @foreach ($question->answers as $answer)
                            <button type="button"
                                    @click="select({{ $question->id }}, {{ $answer->id }})"
                                    :class="answers[{{ $question->id }}] === {{ $answer->id }} ? 'border-brand-600 bg-brand-50' : 'border-slate-200 hover:border-brand-300'"
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
                        class="rounded-full bg-brand-600 px-6 py-2 font-semibold text-white hover:bg-brand-700">
                    See Results
                </button>
            </div>
        </form>

        @include('quizzes.partials.sources')
    </div>
</x-layout>
