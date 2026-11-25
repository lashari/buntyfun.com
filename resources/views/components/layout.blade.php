{{-- title/description must be props: without them Blade drops the values every page passes, and all pages showed "BuntyFun" + the default description (fixed 12 Nov 2026). --}}
@props(['dir' => 'ltr', 'lang' => 'en', 'ads' => true, 'title' => null, 'description' => null,
         'meta' => null, 'breadcrumbs' => [], 'ogType' => 'website', 'ogImage' => null, 'noindex' => false, 'schema' => [], 'urduFont' => false, 'canonical' => true])
@php
    // Per-item SEO overrides (content_meta) win over the page defaults.
    $pageTitle = $meta?->seo_title ? $meta->seo_title.' — BuntyFun' : ($title ?? config('app.name'));
    $pageDesc = $meta?->seo_description ?: ($description ?? 'Play exciting games, discover fun quizzes, create personalized wishes and explore something new every day.');
    // Fallback share image so every link preview shows the brand (logo set 20 Nov 2026).
    $pageImage = $meta?->og_image ?: ($ogImage ?: asset('images/brand/og-default.jpg'));
    $isNoindex = $noindex || ($meta?->noindex ?? false);
    // Page 2+ of a list (only ?page=N, no filters) is its own page: self-canonical + "Page N" in the title,
    // so the items it lists stay reachable for crawlers. Filters/search keep the clean URL as canonical.
    $pageNo = (int) request()->query('page', 1);
    $onlyPage = $pageNo > 1 && array_keys(request()->query()) === ['page'];
    $canonicalUrl = $onlyPage ? url()->current().'?page='.$pageNo : url()->current();
    if ($onlyPage && ! str_contains($pageTitle, 'Page '.$pageNo)) {
        $pageTitle = preg_match('/ [—|] BuntyFun$/u', $pageTitle)
            ? preg_replace('/( [—|] BuntyFun)$/u', ' (Page '.$pageNo.')$1', $pageTitle)
            : $pageTitle.' (Page '.$pageNo.')';
    }
    // No ads on pages we ask search engines not to index (thin, private, utility pages).
    $showAds = $ads && ! $isNoindex;
    // Sections + dropdown links come from one place (App\Content\SiteNav); empty sections are left out there.
    $navGroups = \App\Content\SiteNav::groups();
    $here = url()->current();
    // A section is active on its hub, its menu links, and any page under its hub URL (e.g. /wallpapers/c/airplanes).
    $isActive = fn (array $g) => collect($g['links'])->contains(fn ($l) => $l[0] === $here) || $g['url'] === $here
        || str_starts_with($here, rtrim($g['url'], '/').'/');
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
    @if ($canonical)
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif
    @if ($isNoindex)
        <meta name="robots" content="noindex, follow">
    @endif
    <meta property="og:site_name" content="BuntyFun">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDesc }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
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
    <header class="sticky top-0 z-30 border-b border-brand-100 bg-white/95 backdrop-blur" x-data="{ open: false, search: false,
                toggleMenu() { this.open = ! this.open; this.search = false; if (this.open) this.$nextTick(() => this.$refs.mq.focus()) },
                esc() { if (this.open) { this.open = false; this.$refs.menuBtn.focus() } else if (this.search) { this.search = false; this.$refs.searchBtn.focus() } } }"
            @keydown.escape.window="esc()"
            @focusout="if (open && $event.relatedTarget && ! $el.contains($event.relatedTarget)) open = false">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3">
            <a href="{{ route('home') }}" class="shrink-0" aria-label="BuntyFun home">
                <img src="{{ asset('images/brand/buntyfun-logo.svg') }}" alt="BuntyFun" width="170" height="36" class="hidden h-9 w-auto sm:block">
                <img src="{{ asset('images/brand/buntyfun-mark.svg') }}" alt="BuntyFun" width="47" height="36" class="h-9 w-auto sm:hidden">
            </a>
            {{-- Desktop: each section is a normal link to its hub; the ▾ button opens its dropdown (mouse hover opens it
                 too, without JS; keyboard users press the button, Escape closes). All dropdown links are plain <a href> in the HTML. --}}
            <nav class="hidden min-w-0 xl:block" aria-label="Main">
                <ul class="flex items-center gap-1 whitespace-nowrap text-sm font-semibold text-navy-700">
                    @foreach ($navGroups as $key => $g)
                        <li class="group relative" x-data="{ o: false }" @mouseleave="o = false" @focusout="if (! $el.contains($event.relatedTarget)) o = false" @keydown.escape="o = false; $refs.t.focus()">
                            <div class="flex items-center rounded-lg group-hover:bg-brand-50 {{ $isActive($g) ? 'text-brand-600' : '' }}">
                                <a href="{{ $g['url'] }}" class="py-2 pl-2.5 pr-1 hover:text-brand-600" @if ($g['url'] === $here) aria-current="page" @endif>{{ $g['label'] }}</a>
                                @if (count($g['links']) > 1)
                                    <button type="button" x-ref="t" @click="o = !o" :aria-expanded="o.toString()" aria-controls="nav-{{ $key }}"
                                            class="flex h-8 w-6 items-center justify-center rounded hover:text-brand-600" aria-label="{{ $g['label'] }} menu">
                                        <svg viewBox="0 0 20 20" class="h-4 w-4 transition-transform" :class="o && 'rotate-180'" fill="currentColor" aria-hidden="true"><path d="M5.5 7.5 10 12l4.5-4.5z"/></svg>
                                    </button>
                                @endif
                            </div>
                            @if (count($g['links']) > 1)
                                <ul id="nav-{{ $key }}" class="absolute top-full z-40 hidden w-60 rounded-xl border border-slate-200 bg-white p-2 font-medium text-slate-700 shadow-xl group-hover:block {{ $loop->remaining < 2 ? 'right-0' : 'left-0' }}"
                                    :class="o && '!block'">
                                    @foreach ($g['links'] as [$href, $label])
                                        <li><a href="{{ $href }}" class="block rounded-lg px-3 py-2 hover:bg-brand-50 hover:text-brand-700 {{ $href === $here ? 'bg-brand-50 text-brand-700' : '' }}" @if ($href === $here) aria-current="page" @endif>{{ $label }}</a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>
            <div class="flex shrink-0 items-center gap-2">
                <button type="button" x-ref="searchBtn" @click="search = !search; open = false; if (search) $nextTick(() => $refs.q.focus())" :aria-expanded="search.toString()" aria-controls="site-search-bar"
                        class="flex h-9 w-9 items-center justify-center rounded-full border border-navy-100 bg-navy-50/60 text-navy-700 hover:border-brand-400" aria-label="Search BuntyFun">
                    <svg x-show="!search" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <svg x-show="search" x-cloak viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
                <button type="button" x-ref="menuBtn" @click="toggleMenu()" :aria-expanded="open.toString()" aria-controls="mobile-menu"
                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 xl:hidden">
                    <span x-show="!open">Menu</span><span x-show="open" x-cloak>Close</span>
                </button>
            </div>
        </div>
        <div id="site-search-bar" x-show="search" x-cloak x-transition.opacity class="border-t border-slate-200 bg-white">
            <form action="{{ route('search') }}" method="GET" role="search" class="mx-auto flex max-w-2xl gap-2 px-4 py-3">
                <label for="site-search" class="sr-only">Search BuntyFun</label>
                <input id="site-search" x-ref="q" type="search" name="q" value="{{ request('q') }}" placeholder="Search quizzes, games, quotes, wishes, names…"
                       class="min-w-0 flex-1 rounded-full border border-navy-100 bg-navy-50/60 px-4 py-2 text-base focus:border-brand-500 focus:bg-white focus:outline-none">
                <button type="submit" class="rounded-full bg-brand-600 px-5 py-2 font-semibold text-white hover:bg-brand-700">Search</button>
            </form>
        </div>
        {{-- Mobile/tablet: search first, then one <details> per section (native, keyboard and touch friendly).
             Escape closes and puts focus back on the Menu button; tabbing out of the header closes it too. --}}
        <nav id="mobile-menu" x-show="open" x-cloak class="max-h-[calc(100vh-4rem)] overflow-y-auto border-t border-slate-200 xl:hidden" aria-label="Main mobile">
            <form action="{{ route('search') }}" method="GET" role="search" class="mx-auto flex max-w-6xl gap-2 px-4 pt-3">
                <label for="menu-search" class="sr-only">Search BuntyFun</label>
                <input id="menu-search" x-ref="mq" type="search" name="q" placeholder="Search games, quizzes, wishes…"
                       class="min-w-0 flex-1 rounded-full border border-navy-100 bg-navy-50/60 px-4 py-2 text-base focus:border-brand-500 focus:bg-white focus:outline-none">
                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Search</button>
            </form>
            <ul class="mx-auto grid max-w-6xl gap-1 px-4 py-3 text-sm font-medium text-slate-700 sm:grid-cols-2 lg:grid-cols-3">
                <li><a href="{{ route('home') }}" class="block rounded-lg px-3 py-2.5 font-semibold text-navy-700 hover:bg-brand-50">Home</a></li>
                @foreach ($navGroups as $key => $g)
                    <li>
                        @if (count($g['links']) > 1)
                            <details class="group rounded-lg" @if ($isActive($g)) open @endif>
                                <summary class="flex cursor-pointer list-none items-center justify-between rounded-lg px-3 py-2.5 font-semibold text-navy-700 hover:bg-brand-50 [&::-webkit-details-marker]:hidden">
                                    {{ $g['label'] }}
                                    <svg viewBox="0 0 20 20" class="h-4 w-4 transition-transform group-open:rotate-180" fill="currentColor" aria-hidden="true"><path d="M5.5 7.5 10 12l4.5-4.5z"/></svg>
                                </summary>
                                <ul class="mb-2 ml-3 border-l border-brand-100 pl-2">
                                    @foreach ($g['links'] as [$href, $label])
                                        <li><a href="{{ $href }}" class="block rounded-lg px-3 py-2 hover:bg-brand-50" @if ($href === $here) aria-current="page" @endif>{{ $label }}</a></li>
                                    @endforeach
                                </ul>
                            </details>
                        @else
                            <a href="{{ $g['url'] }}" class="block rounded-lg px-3 py-2.5 font-semibold text-navy-700 hover:bg-brand-50">{{ $g['label'] }}</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
    </header>

    <main>
        @if ($breadcrumbs)
            {{-- Same items, same order as the BreadcrumbList JSON-LD in <head>. --}}
            <nav aria-label="Breadcrumb" class="mx-auto max-w-6xl px-4 pt-4 text-xs text-slate-500">
                <ol class="flex flex-wrap items-center gap-x-1">
                    <li><a href="{{ route('home') }}" class="hover:text-navy-600">Home</a></li>
                    @foreach ($breadcrumbs as $label => $url)
                        <li class="flex items-center gap-x-1"><span aria-hidden="true">›</span>
                            @if ($url && ! $loop->last)<a href="{{ $url }}" class="hover:text-navy-600">{{ $label }}</a>@else<span class="text-slate-700" aria-current="page">{{ $label }}</span>@endif
                        </li>
                    @endforeach
                </ol>
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
            <nav aria-label="Footer" class="grid grid-cols-2 gap-6 sm:grid-cols-4 lg:grid-cols-7">
                @foreach ($navGroups as $g)
                    <div>
                        <a href="{{ $g['url'] }}" class="font-semibold text-white hover:text-brand-400">{{ $g['label'] }}</a>
                        <ul class="mt-2 space-y-1.5">
                            @foreach (collect($g['links'])->reject(fn ($l) => $l[0] === $g['url'])->take(5) as [$href, $label])
                                <li><a href="{{ $href }}" class="hover:text-white">{{ $label }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>
            <div class="mt-8 flex flex-wrap gap-x-4 gap-y-2 border-t border-white/10 pt-6">
                <a href="{{ route('pages.about') }}" class="hover:text-white">About</a>
                <a href="{{ route('pages.contact') }}" class="hover:text-white">Contact</a>
                <a href="{{ route('pages.privacy') }}" class="hover:text-white">Privacy Policy</a>
                <a href="{{ route('pages.terms') }}" class="hover:text-white">Terms of Use</a>
                <a href="{{ route('pages.editorial') }}" class="hover:text-white">Editorial Policy</a>
                @if (config('services.adsense.client_id'))
                    {{-- Re-opens the Google consent message (shown only once the AdSense message is live). --}}
                    <button type="button" class="hover:text-white"
                            onclick="window.googlefc = window.googlefc || {}; googlefc.callbackQueue = googlefc.callbackQueue || []; googlefc.callbackQueue.push(function () { googlefc.showRevocationMessage(); });">Privacy settings</button>
                @endif
            </div>
            <p class="mt-6 text-navy-200">&copy; {{ now()->year }} BuntyFun.com</p>
        </div>
    </footer>
</body>
</html>
