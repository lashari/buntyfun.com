<x-layout :title="$game->title.' — Play Free Online, Rules & Tips | BuntyFun'"
          :breadcrumbs="['Games' => route('games.index'), $game->title => null]"
          :description="$oss['summary'].' Free in your browser, no download or sign-up.'"
          :noindex="$preview">
    <div class="mx-auto max-w-5xl px-4 py-8">
        @if ($preview)
            <p class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm text-amber-800">
                Preview: this game is <strong>{{ Str::headline($game->status) }}</strong> and only admins can see it. Publish it from Admin → Games.
            </p>
        @endif

        <h1 class="text-2xl font-bold">{{ $game->title }}</h1>
        <p class="mt-1 text-slate-600">{{ $oss['summary'] }}</p>
        <p class="mt-2 flex flex-wrap gap-2 text-xs">
            <span class="rounded-full bg-sea-100 px-2 py-0.5 text-sea-800">{{ $oss['genre'] }}</span>
            @foreach (\App\Content\OssGames::audienceOf($game) as $a)
                <a href="{{ route('games.index', ['for' => $a]) }}" class="rounded-full bg-brand-50 px-2 py-0.5 text-brand-700 hover:underline">{{ \App\Content\OssGames::AUDIENCES[$a] }}</a>
            @endforeach
        </p>

        <div x-data="{ fs() { const f = this.$refs.frame; (f.requestFullscreen || f.webkitRequestFullscreen || (() => {})).call(f); } }" class="mt-5">
            {{-- Phones get a taller box for games whose start screen does not fit a short 16:9 frame. --}}
            <style>@media (max-width: 640px) { .game-box { aspect-ratio: var(--ratio-mobile) !important; } }</style>
            <div class="game-box overflow-hidden rounded-xl border border-slate-200 bg-black shadow-sm"
                 style="aspect-ratio: {{ $oss['ratio'] ?? '16/9' }}; --ratio-mobile: {{ $oss['ratio_mobile'] ?? $oss['ratio'] ?? '16/9' }}; max-height: 80vh; margin-inline: auto;">
                <iframe x-ref="frame" src="{{ $src }}" title="{{ $game->title }}" class="h-full w-full"
                        loading="lazy" allow="fullscreen; autoplay" allowfullscreen
                        sandbox="allow-scripts allow-same-origin allow-pointer-lock"></iframe>
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-sm">
                <button type="button" @click="fs()" class="rounded-full bg-brand-600 px-5 py-2 font-semibold text-white hover:bg-brand-700">Full screen</button>
                @if (! empty($oss['keyboard']))
                    <span class="text-slate-500">Needs a keyboard: best on a computer. Click the game first so it gets your key presses.</span>
                @endif
            </div>
        </div>

        <p class="mt-6 text-xs text-slate-500">
            Game by {{ $oss['author'] }}, open source under the {{ $oss['license'] }} licence.
            <a href="{{ $oss['repo'] }}" rel="nofollow noopener" target="_blank" class="underline">Source code</a>.
            The licence text ships with the game files.
        </p>
        <a href="{{ route('games.index') }}" class="mt-4 inline-block text-sm text-slate-500 hover:underline">More games</a>
    </div>
    @include('games.partials.guide')
</x-layout>
