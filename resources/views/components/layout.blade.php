{{-- title/description must be props: without them Blade drops the values every page passes, and all pages showed "BuntyFun" + the default description (fixed 12 Nov 2026). --}}
@props(['dir' => 'ltr', 'lang' => 'en', 'ads' => true, 'title' => null, 'description' => null,
         'meta' => null, 'breadcrumbs' => [], 'ogType' => 'website', 'ogImage' => null, 'noindex' => false, 'schema' => [], 'urduFont' => false])
@php
    // Per-item SEO overrides (content_meta) win over the page defaults.
    $pageTitle = $meta?->seo_title ? $meta->seo_title.' — BuntyFun' : ($title ?? config('app.name'));
    $pageDesc = $meta?->seo_description ?: ($description ?? 'Play exciting games, discover fun quizzes, create personalized wishes and explore something new every day.');
    // Fallback share image so every link preview shows the brand (logo set 20 Nov 2026).
    $pageImage = $meta?->og_image ?: ($ogImage ?: asset('images/brand/og-default.jpg'));
    $isNoindex = $noindex || ($meta?->noindex ?? false);
    // No ads on pages we ask search engines not to index (thin, private, utility pages).
    $showAds = $ads && ! $isNoindex;
    // Hide sections that have nothing published yet (an empty section reads as unfinished).
    $nav = \Illuminate\Support\Facades\Cache::remember('layout.nav-sections', 600, fn () => [
        'messages' => \App\Models\Message::where('status', 'published')->exists(),
        'wallpapers' => \App\Models\Wallpaper::where('status', 'published')->exists(),
        'quotes' => \Illuminate\Support\Facades\Schema::hasTable('quotes') && \App\Models\Quote::where('status', 'published')->exists(),
    ]);
    $links = array_filter([
        [route('daily'), 'Daily'],
        [route('games.index'), 'Games'],
        [route('quizzes.index'), 'Quizzes'],
        $nav['quotes'] ? [route('quotes.index'), 'Quotes'] : null,
        [route('birthday-messages.index'), 'Birthday Wishes'],
        $nav['messages'] ? [route('messages.index'), 'Messages'] : null,
        [route('name-entries.index'), 'Islamic Names'],
        [route('urdu-content.index'), 'اردو'],
        $nav['wallpapers'] ? [route('wallpapers.index'), 'Wallpapers'] : null,
        [route('ai-tools.index'), 'AI Fun'],
    ]);
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
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
    <link rel="icon" href="{{ asset('images/brand/icon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('images/brand/apple-touch-icon.png') }}">
    <meta name="theme-color" content="#0B2D5B">
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
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet">
    @if ($dir === 'rtl' || $urduFont)
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=noto-nastaliq-urdu" rel="stylesheet">
    @endif
    {{-- Google Consent Mode v2: denied by default in the EEA, UK and Switzerland until the
         Google-certified consent message (AdSense → Privacy & messaging) records a choice. --}}
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('consent', 'default', {
            ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied', analytics_storage: 'denied',
            region: ['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IS','IE','IT','LV','LI','LT','LU','MT','NL','NO','PL','PT','RO','SK','SI','ES','SE','GB','CH'],
            wait_for_update: 500
        });
        gtag('consent', 'default', { ad_storage: 'granted', ad_user_data: 'granted', ad_personalization: 'granted', analytics_storage: 'granted' });
    </script>
    @if (config('services.ga4.measurement_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.ga4.measurement_id') }}"></script>
        <script>
            gtag('js', new Date());
            gtag('config', '{{ config('services.ga4.measurement_id') }}');
        </script>
    @endif
    @if ($showAds && config('services.adsense.client_id'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('services.adsense.client_id') }}" crossorigin="anonymous"></script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream text-slate-800 antialiased {{ $dir === 'rtl' ? 'font-urdu' : '' }}">
    <header class="sticky top-0 z-30 border-b border-brand-100 bg-white/95 backdrop-blur" x-data="{ open: false }">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-4">
            <a href="{{ route('home') }}" class="shrink-0" aria-label="BuntyFun home">
                <img src="{{ asset('images/brand/buntyfun-logo.svg') }}" alt="BuntyFun" width="170" height="36" class="hidden h-9 w-auto sm:block">
                <img src="{{ asset('images/brand/buntyfun-mark.svg') }}" alt="BuntyFun" width="47" height="36" class="h-9 w-auto sm:hidden">
            </a>
            <nav class="hidden items-center gap-5 text-sm font-semibold text-navy-700 lg:flex" aria-label="Main">
                @foreach ($links as [$href, $label])
                    <a href="{{ $href }}" class="hover:text-navy-600">{{ $label }}</a>
                @endforeach
            </nav>
            <div class="flex items-center gap-2">
                <form action="{{ route('search') }}" method="GET" class="flex items-center gap-2" role="search">
                    <label for="site-search" class="sr-only">Search BuntyFun</label>
                    <input id="site-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search"
                           class="w-28 rounded-full border border-navy-100 bg-navy-50/60 px-3 py-1.5 text-sm focus:border-brand-500 focus:bg-white focus:outline-none sm:w-44">
                    <button type="submit" class="rounded-full bg-brand-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-brand-700">Go</button>
                </form>
                <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-menu"
                        class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm font-medium text-slate-700 lg:hidden">
                    <span x-show="!open">Menu</span><span x-show="open" x-cloak>Close</span>
                </button>
            </div>
        </div>
        <nav id="mobile-menu" x-show="open" x-cloak class="border-t border-slate-200 lg:hidden" aria-label="Main mobile">
            <div class="mx-auto grid max-w-6xl grid-cols-2 gap-1 px-4 py-3 text-sm font-medium text-slate-700">
                <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 hover:bg-brand-50">Home</a>
                @foreach ($links as [$href, $label])
                    <a href="{{ $href }}" class="rounded-lg px-3 py-2 hover:bg-brand-50">{{ $label }}</a>
                @endforeach
            </div>
        </nav>
    </header>

    <main>
        @if ($breadcrumbs)
            <nav aria-label="Breadcrumb" class="mx-auto max-w-6xl px-4 pt-4 text-xs text-slate-500">
                <a href="{{ route('home') }}" class="hover:text-navy-600">Home</a>
                @foreach ($breadcrumbs as $label => $url)
                    <span class="mx-1">›</span>
                    @if ($url && ! $loop->last)<a href="{{ $url }}" class="hover:text-navy-600">{{ $label }}</a>@else<span class="text-slate-700">{{ $label }}</span>@endif
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

    <x-ad-slot :enabled="$showAds" />

    <footer class="mt-16 bg-navy-700 text-navy-100">
        <div class="h-1.5 bg-linear-to-r from-brand-500 via-sun-400 to-sea-500"></div>
        <div class="mx-auto max-w-6xl px-4 py-10 text-sm">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <img src="{{ asset('images/brand/buntyfun-logo-dark.svg') }}" alt="BuntyFun" width="170" height="36" class="h-9 w-auto" loading="lazy">
                <p class="font-semibold tracking-wide text-white">Play <span class="text-brand-400">•</span> Discover <span class="text-brand-400">•</span> Celebrate <span class="text-brand-400">•</span> Enjoy</p>
            </div>
            <div class="flex flex-wrap gap-x-4 gap-y-2">
                <a href="{{ route('home') }}" class="hover:text-navy-600">Home</a>
                @foreach ($links as [$href, $label])
                    <a href="{{ $href }}" class="hover:text-navy-600">{{ $label }}</a>
                @endforeach
            </div>
            <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2">
                <a href="{{ route('pages.about') }}" class="hover:text-navy-600">About</a>
                <a href="{{ route('pages.contact') }}" class="hover:text-navy-600">Contact</a>
                <a href="{{ route('pages.privacy') }}" class="hover:text-navy-600">Privacy Policy</a>
                <a href="{{ route('pages.terms') }}" class="hover:text-navy-600">Terms of Use</a>
                <a href="{{ route('pages.editorial') }}" class="hover:text-navy-600">Editorial Policy</a>
                @if (config('services.adsense.client_id'))
                    {{-- Re-opens the Google consent message (shown only once the AdSense message is live). --}}
                    <button type="button" class="hover:text-navy-600"
                            onclick="window.googlefc = window.googlefc || {}; googlefc.callbackQueue = googlefc.callbackQueue || []; googlefc.callbackQueue.push(function () { googlefc.showRevocationMessage(); });">Privacy settings</button>
                @endif
            </div>
            <p class="mt-6 text-navy-200">&copy; {{ now()->year }} BuntyFun.com</p>
        </div>
    </footer>
</body>
</html>
