@extends('layouts.app')

@section('title', 'Record contribution')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-6"><a href="{{ route('contributions.index') }}" class="text-sm font-medium text-green-700 hover:text-green-800">← Back to contributions</a><h1 class="mt-3 text-2xl font-bold text-slate-900">Record a payment</h1><p class="mt-1 text-sm text-slate-500">The payment will be pending until a Treasurer verifies it.</p></div>
    <form method="POST" action="{{ route('contributions.store') }}" class="card space-y-5">
        @csrf
        <div><label for="member_id" class="field-label">Member</label><select id="member_id" name="member_id" required class="field-input"><option value="">Select a member</option>@foreach ($members as $member)<option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>{{ $member->user->name }} · {{ $member->user->phone }}</option>@endforeach</select></div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div><label for="amount" class="field-label">Amount (KSh)</label><input id="amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount', $chama->monthly_amount) }}" required class="field-input"></div>
            <div><label for="month" class="field-label">Contribution month</label><input id="month" name="month" type="month" value="{{ old('month', now()->format('Y-m')) }}" required class="field-input"></div>
            <div><label for="payment_method" class="field-label">Payment method</label><select id="payment_method" name="payment_method" required class="field-input"><option value="mpesa" @selected(old('payment_method') === 'mpesa')>M-Pesa</option><option value="cash" @selected(old('payment_method') === 'cash')>Cash</option><option value="bank" @selected(old('payment_method') === 'bank')>Bank</option></select></div>
            <div><label for="mpesa_code" class="field-label">M-Pesa transaction code</label><input id="mpesa_code" name="mpesa_code" value="{{ old('mpesa_code') }}" maxlength="20" placeholder="e.g. QGH7ABC123" class="field-input"><p class="mt-1 text-xs text-slate-500">Required for M-Pesa payments.</p></div>
            <div class="sm:col-span-2"><label for="payment_date" class="field-label">Payment date</label><input id="payment_date" name="payment_date" type="date" value="{{ old('payment_date', today()->format('Y-m-d')) }}" max="{{ today()->format('Y-m-d') }}" required class="field-input"></div>
        </div>
        <div class="flex justify-end"><button type="submit" class="btn-primary">Save as pending</button></div>
    </form>
</div>
@endsection
