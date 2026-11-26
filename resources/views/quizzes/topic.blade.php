@php $label = $info['label']; @endphp
<x-layout :title="$label.' Quizzes — '.$quizzes->count().' Free Quizzes With Answers | BuntyFun'"
          :description="Str::limit($info['intro'], 120).' '.$quizzes->count().' free quizzes, answers explained.'"
          :noindex="$quizzes->count() < 2"
          :schema="$quizzes->count() < 2 ? [] : \App\Support\Schema::itemList($label.' quizzes', $quizzes->map(fn ($q) => [route('quizzes.show', $q), $q->title]))"
          :breadcrumbs="['Quizzes' => route('quizzes.index'), $label => null]">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ $info['emoji'] }} {{ $label }} Quizzes</h1>
        <p class="mt-3 max-w-3xl leading-relaxed text-slate-700">{{ $info['intro'] }}</p>
        <p class="mt-2 text-sm text-slate-500">{{ $quizzes->count() }} {{ Str::plural('quiz', $quizzes->count()) }} · {{ $quizzes->sum('questions_count') }} questions · easiest first</p>

        @include('quizzes.partials.topic-chips', ['current' => $topic])

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($quizzes as $quiz)
                @include('quizzes.partials.card')
            @endforeach
        </div>

        <section class="prose-page mt-12 max-w-3xl">
            <h2>How these {{ strtolower($label) }} quizzes work</h2>
            <p>Each quiz has about ten multiple-choice questions and takes two to four minutes. Pick an answer to move to the next question. At the end you see your score and a short explanation for every question, including the ones you got right.</p>
            <p>Questions come from open trivia collections and our own writers. We check each one before it goes live, fix mistakes, and add the explanations ourselves. Sources are listed at the bottom of every quiz. If you find an error, <a href="{{ route('pages.contact') }}">tell us</a>.</p>
            <p>Want more? Try <a href="{{ route('daily') }}">today's Daily Challenge</a> or one of our <a href="{{ route('games.index') }}">free brain games</a>.</p>
        </section>
    </div>
</x-layout>
