{{-- title/description must be props: without them Blade drops the values every page passes, and all pages showed "BuntyFun" + the default description (fixed 12 Nov 2026). --}}
@props(['dir' => 'ltr', 'lang' => 'en', 'ads' => true, 'title' => null, 'description' => null,
         'meta' => null, 'breadcrumbs' => [], 'ogType' => 'website', 'ogImage' => null, 'noindex' => false, 'schema' => []])
@php
    // Per-item SEO overrides (content_meta) win over the page defaults.
    $pageTitle = $meta?->seo_title ? $meta->seo_title.' — BuntyFun' : ($title ?? config('app.name'));
    $pageDesc = $meta?->seo_description ?: ($description ?? 'Play exciting games, discover fun quizzes, create personalized wishes and explore something new every day.');
    $pageImage = $meta?->og_image ?: $ogImage;
    $isNoindex = $noindex || ($meta?->noindex ?? false);
    $faq = collect($meta?->faq ?? [])->filter(fn ($f) => ! empty($f['q']) && ! empty($f['a']))->values();
    $ld = $schema;
    if (request()->routeIs('home')) {
        $ld[] = ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => 'BuntyFun', 'url' => route('home'),
            'potentialAction' => ['@type' => 'SearchAction', 'target' => route('search').'?q={search_term_string}', 'query-input' => 'required name=search_term_string']];
    }
    if ($breadcrumbs) {
        $crumbs = array_merge(['Home' => route('home')], $breadcrumbs);
        $ld[] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => collect($crumbs)->keys()->map(fn ($label, $i) => array_filter([
            '@type' => 'ListItem', 'position' => $i + 1, 'name' => $label, 'item' => $crumbs[$label] ?: null,
        ]))->all()];
    }
    if ($faq->isNotEmpty()) {
        $ld[] = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faq->map(fn ($f) => [
            '@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
        ])->all()];
    }
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $dir }}">
<head><meta charset="utf-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @if ($isNoindex)
        <meta name="robots" content="noindex, follow">
    @endif
    <meta property="og:site_name" content="BuntyFun">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDesc }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ $lang === 'ur' ? 'ur_PK' : 'en_US' }}">
    @if ($pageImage)
        <meta property="og:image" content="{{ $pageImage }}">
    @endif
    <meta name="twitter:card" content="{{ $pageImage ? 'summary_large_image' : 'summary' }}">
    <link rel="alternate" type="application/rss+xml" title="BuntyFun — new content" href="{{ route('feed') }}">
    @foreach ($ld as $block)
        <script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endforeach
    @if ($dir === 'rtl')
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=noto-nastaliq-urdu" rel="stylesheet">
    @endif
    @if (config('services.ga4.measurement_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.ga4.measurement_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ config('services.ga4.measurement_id') }}');
        </script>
    @endif
    @if ($ads && config('services.adsense.client_id'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('services.adsense.client_id') }}" crossorigin="anonymous"></script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased {{ $dir === 'rtl' ? 'font-urdu' : '' }}">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
            <a href="{{ route('home') }}" class="text-xl font-bold text-violet-600">BuntyFun</a>
            <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
                <a href="{{ route('home') }}" class="hover:text-violet-600">Home</a>
                <a href="{{ route('games.index') }}" class="hover:text-violet-600">Games</a>
                <a href="{{ route('quizzes.index') }}" class="hover:text-violet-600">Quizzes</a>
                <a href="{{ route('birthday-messages.index') }}" class="hover:text-violet-600">Birthday Wishes</a>
                <a href="{{ route('messages.index') }}" class="hover:text-violet-600">Messages</a>
                <a href="{{ route('name-entries.index') }}" class="hover:text-violet-600">Islamic Names</a>
                <a href="{{ route('urdu-content.index') }}" class="hover:text-violet-600">اردو</a>
                <a href="{{ route('wallpapers.index') }}" class="hover:text-violet-600">Wallpapers</a>
                <a href="{{ route('ai-tools.index') }}" class="hover:text-violet-600">AI Fun</a>
            </nav>
            <form action="{{ route('search') }}" method="GET" class="flex items-center gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search BuntyFun"
                       class="w-40 rounded-full border border-slate-300 px-3 py-1.5 text-sm focus:border-violet-500 focus:outline-none md:w-56">
                <button type="submit" class="rounded-full bg-violet-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-violet-700">Go</button>
            </form>
        </div>
    </header>

    <x-ad-slot :enabled="$ads" />

    <main>
        @if ($breadcrumbs)
            <nav aria-label="Breadcrumb" class="mx-auto max-w-6xl px-4 pt-4 text-xs text-slate-500">
                <a href="{{ route('home') }}" class="hover:text-violet-600">Home</a>
                @foreach ($breadcrumbs as $label => $url)
                    <span class="mx-1">›</span>
                    @if ($url && ! $loop->last)<a href="{{ $url }}" class="hover:text-violet-600">{{ $label }}</a>@else<span class="text-slate-700">{{ $label }}</span>@endif
                @endforeach
            </nav>
        @endif

        {{ $slot }}

        @if ($faq->isNotEmpty())
            <section class="mx-auto max-w-xl px-4 pb-8" dir="ltr">
                <h2 class="text-lg font-bold">Questions</h2>
                @foreach ($faq as $f)
                    <details class="mt-3 rounded-lg border border-slate-200 bg-white p-4">
                        <summary class="cursor-pointer font-medium">{{ $f['q'] }}</summary>
                        <p class="mt-2 text-sm text-slate-600">{{ $f['a'] }}</p>
                    </details>
                @endforeach
            </section>
        @endif
    </main>

    <x-ad-slot :enabled="$ads" />

    <footer class="mt-16 border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-8 text-sm text-slate-500">
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('home') }}" class="hover:text-violet-600">Home</a>
                <a href="{{ route('games.index') }}" class="hover:text-violet-600">Games</a>
                <a href="{{ route('quizzes.index') }}" class="hover:text-violet-600">Quizzes</a>
                <a href="{{ route('birthday-messages.index') }}" class="hover:text-violet-600">Birthday Wishes</a>
                <a href="{{ route('messages.index') }}" class="hover:text-violet-600">Messages</a>
                <a href="{{ route('name-entries.index') }}" class="hover:text-violet-600">Islamic Names</a>
                <a href="{{ route('urdu-content.index') }}" class="hover:text-violet-600">اردو</a>
                <a href="{{ route('wallpapers.index') }}" class="hover:text-violet-600">Wallpapers</a>
                <a href="{{ route('ai-tools.index') }}" class="hover:text-violet-600">AI Fun</a>
            </div>
            <p class="mt-4">&copy; {{ now()->year }} BuntyFun.com</p>
        </div>
    </footer>
</body>
</html>
