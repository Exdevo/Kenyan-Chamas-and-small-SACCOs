<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'Chama Ledger') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <div class="min-h-screen lg:flex">
        <aside class="no-print hidden w-64 shrink-0 border-r border-slate-200 bg-white lg:flex lg:flex-col">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 border-b border-slate-100 px-6 py-5">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-green-700 text-sm font-bold text-white">CL</span>
                <span>
                    <span class="block text-sm font-bold text-slate-900">Chama Ledger</span>
                    <span class="block text-xs text-slate-500">Simple. Clear. Together.</span>
                </span>
            </a>
            <nav class="flex-1 space-y-1 px-4 py-5">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span aria-hidden="true">⌂</span> Overview
                </a>
                @if (in_array(auth()->user()->role, ['admin', 'treasurer'], true))
                    <a href="{{ route('members.index') }}" class="nav-link {{ request()->routeIs('members.*') ? 'active' : '' }}">
                        <span aria-hidden="true">♧</span> Members
                    </a>
                @endif
                <a href="{{ route('contributions.index') }}" class="nav-link {{ request()->routeIs('contributions.*') ? 'active' : '' }}">
                    <span aria-hidden="true">↗</span> Contributions
                </a>
                <a href="{{ route('loans.index') }}" class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
                    <span aria-hidden="true">▤</span> Loans
                </a>
                @if (auth()->user()->role === 'member')
                    <a href="{{ route('statement.download') }}" class="nav-link">
                        <span aria-hidden="true">⇩</span> Download statement
                    </a>
                @endif
            </nav>
            <div class="border-t border-slate-100 p-4">
                <div class="mb-3 truncate px-3 text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</div>
                <div class="mb-3 px-3 text-xs capitalize text-slate-500">{{ auth()->user()->role }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="nav-link w-full text-left" type="submit"><span aria-hidden="true">↪</span> Sign out</button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="no-print sticky top-0 z-10 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-7">
                <button class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" type="button" data-menu-button aria-expanded="false" aria-label="Toggle navigation">☰</button>
                <div class="hidden min-w-0 sm:block">
                    <p class="truncate text-sm font-semibold text-slate-900">@yield('title', 'Dashboard')</p>
                    <p class="truncate text-xs text-slate-500">{{ auth()->user()->chama()?->name ?? 'Your Chama workspace' }}</p>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <span class="hidden text-right sm:block">
                        <span class="block text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</span>
                        <span class="block text-xs capitalize text-slate-500">{{ auth()->user()->role }}</span>
                    </span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-green-100 text-sm font-bold text-green-800">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                </div>
            </header>

            <div class="no-print hidden border-b border-slate-200 bg-white px-4 py-3 lg:hidden" data-mobile-menu>
                <nav class="space-y-1">
                    <a href="{{ route('dashboard') }}" class="nav-link">Overview</a>
                    @if (in_array(auth()->user()->role, ['admin', 'treasurer'], true))
                        <a href="{{ route('members.index') }}" class="nav-link">Members</a>
                    @endif
                    <a href="{{ route('contributions.index') }}" class="nav-link">Contributions</a>
                    <a href="{{ route('loans.index') }}" class="nav-link">Loans</a>
                    @if (auth()->user()->role === 'member')
                        <a href="{{ route('statement.download') }}" class="nav-link">Download statement</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-link w-full text-left" type="submit">Sign out</button></form>
                </nav>
            </div>

            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-7 sm:py-8">
                @if (session('success'))
                    <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        <p class="font-semibold">Please check the following:</p>
                        <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
