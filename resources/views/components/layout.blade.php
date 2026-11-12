{{-- title/description must be props: without them Blade drops the values every page passes, and all pages showed "BuntyFun" + the default description (fixed 12 Nov 2026). --}}
@props(['dir' => 'ltr', 'lang' => 'en', 'ads' => true, 'title' => null, 'description' => null])
<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $dir }}">
<head><meta charset="utf-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $description ?? 'Play exciting games, discover fun quizzes, create personalized wishes and explore something new every day.' }}">
    <link rel="canonical" href="{{ url()->current() }}">
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
        {{ $slot }}
    </main>

    <x-ad-slot :enabled="$ads" />

    <footer class="mt-16 border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-8 text-sm text-slate-500">
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('home') }}" class="hover:text-violet-600">Home</a>
                <a href="{{ route('games.index') }}" class="hover:text-violet-600">Games</a>
                <a href="{{ route('quizzes.index') }}" class="hover:text-violet-600">Quizzes</a>
                <a href="{{ route('birthday-messages.index') }}" class="hover:text-violet-600">Birthday Wishes</a>
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
