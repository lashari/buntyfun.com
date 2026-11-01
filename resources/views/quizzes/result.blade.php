<x-layout :title="$quiz->title.' — Result — BuntyFun'" description="See your quiz result.">
    <div class="mx-auto max-w-xl px-4 py-16 text-center">
        <p class="text-sm font-medium text-violet-600">{{ $quiz->title }}</p>
        <h1 class="mt-2 text-3xl font-extrabold">{{ $result->outcome_label }}</h1>

        <div class="mt-8 flex flex-wrap justify-center gap-3"
             x-data="{
                 url: window.location.href,
                 copied: false,
                 copy() {
                     navigator.clipboard.writeText(this.url);
                     this.copied = true;
                     setTimeout(() => this.copied = false, 2000);
                 },
             }">
            <a :href="`https://wa.me/?text=${encodeURIComponent('{{ $quiz->title }}: {{ $result->outcome_label }} ' + window.location.href)}`"
               target="_blank" rel="noopener"
               class="rounded-full bg-emerald-600 px-5 py-2 font-semibold text-white hover:bg-emerald-700">
                Share on WhatsApp
            </a>
            <button type="button" @click="copy()" class="rounded-full border border-slate-300 px-5 py-2 font-semibold hover:bg-slate-50">
                <span x-show="!copied">Copy link</span>
                <span x-show="copied" x-cloak>Copied!</span>
            </button>
        </div>

        <a href="{{ route('quizzes.show', $quiz) }}" class="mt-8 block text-sm text-slate-500 hover:underline">Retake this quiz</a>
        <a href="{{ route('quizzes.index') }}" class="mt-2 block text-sm text-slate-500 hover:underline">More quizzes</a>
    </div>
</x-layout>
