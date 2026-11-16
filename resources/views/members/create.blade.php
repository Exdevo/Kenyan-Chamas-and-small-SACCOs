@extends('layouts.app')

@section('title', 'Add a member')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-6"><a href="{{ route('members.index') }}" class="text-sm font-medium text-green-700 hover:text-green-800">← Back to members</a><h1 class="mt-3 text-2xl font-bold text-slate-900">Add a member</h1><p class="mt-1 text-sm text-slate-500">Create a secure account and link it to {{ auth()->user()->chama()->name }}.</p></div>
    <form method="POST" action="{{ route('members.store') }}" class="card">
        @csrf
        <div class="grid gap-5 sm:grid-cols-2">
            <div><label for="name" class="field-label">Full name</label><input id="name" name="name" value="{{ old('name') }}" required class="field-input"></div>
            <div><label for="phone" class="field-label">Phone number</label><input id="phone" name="phone" value="{{ old('phone') }}" required placeholder="07XX XXX XXX" class="field-input"></div>
            <div><label for="email" class="field-label">Email address (sign-in)</label><input id="email" name="email" type="email" value="{{ old('email') }}" required class="field-input"></div>
            <div><label for="id_number" class="field-label">National ID number</label><input id="id_number" name="id_number" value="{{ old('id_number') }}" required class="field-input"></div>
            <div><label for="password" class="field-label">Initial password</label><input id="password" name="password" type="password" required autocomplete="new-password" class="field-input"><p class="mt-1 text-xs text-slate-500">Share this securely with the member; they can sign in to view their records.</p></div>
            <div><label for="password_confirmation" class="field-label">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="field-input"></div>
            <div><label for="joined_date" class="field-label">Joined date</label><input id="joined_date" name="joined_date" type="date" value="{{ old('joined_date', today()->format('Y-m-d')) }}" max="{{ today()->format('Y-m-d') }}" required class="field-input"></div>
            <div><label for="status" class="field-label">Membership status</label><select id="status" name="status" required class="field-input"><option value="active" @selected(old('status', 'active') === 'active')>Active</option><option value="inactive" @selected(old('status') === 'inactive')>Inactive</option></select></div>
            <div class="sm:col-span-2"><label for="role" class="field-label">Access role</label><select id="role" name="role" required class="field-input"><option value="member" @selected(old('role', 'member') === 'member')>Member — view own records and request loans</option><option value="treasurer" @selected(old('role') === 'treasurer')>Treasurer — confirm payments and review loans</option></select></div>
        </div>
        <div class="mt-6 flex justify-end"><button type="submit" class="btn-primary">Create member account</button></div>
    </form>
</div>
@endsection
