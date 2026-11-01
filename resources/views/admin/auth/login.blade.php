<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — BuntyFun</title>
    <meta name="robots" content="noindex">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100">
    <form method="POST" action="{{ route('admin.login') }}" class="w-full max-w-sm rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
        @csrf
        <h1 class="text-xl font-bold text-violet-600">BuntyFun Admin</h1>

        @if ($errors->any())
            <div class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <label class="mt-6 block text-sm font-medium text-slate-700">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-violet-500 focus:outline-none">

        <label class="mt-4 block text-sm font-medium text-slate-700">Password</label>
        <input type="password" name="password" required
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-violet-500 focus:outline-none">

        <button type="submit" class="mt-6 w-full rounded-lg bg-violet-600 py-2 font-semibold text-white hover:bg-violet-700">
            Log in
        </button>
    </form>
</body>
</html>
