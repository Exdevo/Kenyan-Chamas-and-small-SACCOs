@extends('layouts.guest')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm sm:p-8">
        @if (! $registrationOpen)
            <h1 class="text-2xl font-bold text-slate-900">Registration is closed</h1>
            <p class="mt-2 text-sm text-slate-600">For account safety, only the first administrator can register here. Ask your Chama administrator to add your account.</p>
            <a href="{{ route('login') }}" class="btn-primary mt-6 w-full">Go to sign in</a>
        @else
            <h1 class="text-2xl font-bold text-slate-900">Create your admin account</h1>
            <p class="mt-1 text-sm text-slate-500">This account will set up and manage your Chama.</p>
            <form method="POST" action="{{ route('register.store') }}" class="mt-7 space-y-4">
                @csrf
                <div><label for="name" class="field-label">Full name</label><input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="field-input"></div>
                <div><label for="phone" class="field-label">Phone number</label><input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="07XX XXX XXX" class="field-input"></div>
                <div><label for="email" class="field-label">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="field-input"></div>
                <div><label for="password" class="field-label">Password</label><input id="password" name="password" type="password" required autocomplete="new-password" class="field-input"></div>
                <div><label for="password_confirmation" class="field-label">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="field-input"></div>
                <button type="submit" class="btn-primary w-full">Create admin account</button>
            </form>
        @endif
    </div>
@endsection
