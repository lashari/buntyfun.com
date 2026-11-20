<x-layout title="Page not found — BuntyFun" description="This page could not be found." :noindex="true" :ads="false">
    <div class="mx-auto max-w-xl px-4 py-20 text-center">
        <h1 class="text-3xl font-bold">Page not found</h1>
        <p class="mt-3 text-slate-600">The page may have moved or been removed. Try one of these instead:</p>
        <div class="mt-6 flex flex-wrap justify-center gap-2 text-sm">
            <a href="{{ route('birthday-messages.index') }}" class="rounded-full border border-slate-300 bg-white px-4 py-1.5 hover:border-violet-300">Birthday wishes</a>
            <a href="{{ route('name-entries.index') }}" class="rounded-full border border-slate-300 bg-white px-4 py-1.5 hover:border-violet-300">Islamic names</a>
            <a href="{{ route('games.index') }}" class="rounded-full border border-slate-300 bg-white px-4 py-1.5 hover:border-violet-300">Games</a>
            <a href="{{ route('quizzes.index') }}" class="rounded-full border border-slate-300 bg-white px-4 py-1.5 hover:border-violet-300">Quizzes</a>
        </div>
    </div>
</x-layout>
