<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin' }} — BuntyFun Admin</title>
    <meta name="robots" content="noindex">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="flex min-h-screen">
        <aside class="w-56 shrink-0 border-r border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-4 text-lg font-bold text-violet-600">BuntyFun Admin</div>
            <nav class="flex flex-col gap-1 p-3 text-sm font-medium text-slate-600">
                <a href="{{ route('admin.dashboard') }}" class="rounded px-3 py-2 hover:bg-violet-50 hover:text-violet-700">Dashboard</a>
                <a href="{{ route('admin.quizzes.index') }}" class="rounded px-3 py-2 hover:bg-violet-50 hover:text-violet-700">Quizzes</a>
                <a href="{{ route('admin.games.index') }}" class="rounded px-3 py-2 hover:bg-violet-50 hover:text-violet-700">Games</a>
                <a href="{{ route('admin.birthday-messages.index') }}" class="rounded px-3 py-2 hover:bg-violet-50 hover:text-violet-700">Birthday Wishes</a>
                <a href="{{ route('admin.name-entries.index') }}" class="rounded px-3 py-2 hover:bg-violet-50 hover:text-violet-700">Islamic Names</a>
                @auth
                    @if (auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.categories.index') }}" class="rounded px-3 py-2 hover:bg-violet-50 hover:text-violet-700">Categories</a>
                    @endif
                @endauth
            </nav>
            <form method="POST" action="{{ route('admin.logout') }}" class="p-3">
                @csrf
                <button type="submit" class="w-full rounded px-3 py-2 text-left text-sm text-slate-500 hover:bg-slate-50">
                    Log out ({{ auth()->user()?->name }})
                </button>
            </form>
        </aside>

        <div class="flex-1 p-6">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</body>
</html>
