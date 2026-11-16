<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Welcome' }} · {{ config('app.name', 'Chama Ledger') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10">
    <main class="w-full max-w-md">
        <a href="{{ route('login') }}" class="mb-7 flex items-center justify-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-green-700 font-bold text-white">CL</span>
            <span class="text-xl font-bold text-slate-900">Chama Ledger</span>
        </a>
        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
