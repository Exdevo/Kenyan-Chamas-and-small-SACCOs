@extends('layouts.guest')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm sm:p-8">
        <h1 class="text-2xl font-bold text-slate-900">Welcome back</h1>
        <p class="mt-1 text-sm text-slate-500">Sign in to your Chama account.</p>
        <form method="POST" action="{{ route('login.store') }}" class="mt-7 space-y-5">
            @csrf
            <div>
                <label for="email" class="field-label">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="field-input">
            </div>
            <div>
                <label for="password" class="field-label">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="field-input">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600"><input name="remember" type="checkbox" class="rounded border-slate-300 text-green-700 focus:ring-green-600"> Remember me</label>
            <button type="submit" class="btn-primary w-full">Sign in</button>
        </form>
        @if (! \App\Models\User::exists())
            <p class="mt-6 text-center text-sm text-slate-500">Setting up a new group? <a href="{{ route('register') }}" class="font-semibold text-green-700 hover:text-green-800">Create the admin account</a></p>
        @endif
    </div>
@endsection
