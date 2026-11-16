@extends('layouts.app')

@section('title', 'Request a loan')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-6"><a href="{{ route('loans.index') }}" class="text-sm font-medium text-green-700 hover:text-green-800">← Back to loans</a><h1 class="mt-3 text-2xl font-bold text-slate-900">Request a loan</h1><p class="mt-1 text-sm text-slate-500">A flat 10% interest rate is added if the Treasurer approves your request.</p></div>
    <form method="POST" action="{{ route('loans.store') }}" class="card space-y-5">
        @csrf
        <div><label for="amount" class="field-label">Amount requested (KSh)</label><input id="amount" name="amount" type="number" step="0.01" min="1" value="{{ old('amount') }}" required class="field-input"><p class="mt-1 text-xs text-slate-500">For example, KSh 10,000 becomes KSh 11,000 to repay.</p></div>
        <div><label for="reason" class="field-label">Reason for the loan</label><textarea id="reason" name="reason" rows="3" maxlength="2000" required class="field-input">{{ old('reason') }}</textarea></div>
        <div><label for="guarantor_id" class="field-label">Guarantor</label><select id="guarantor_id" name="guarantor_id" required class="field-input"><option value="">Choose another active member</option>@foreach ($guarantors as $guarantor)<option value="{{ $guarantor->id }}" @selected(old('guarantor_id') == $guarantor->id)>{{ $guarantor->user->name }}</option>@endforeach</select></div>
        <div><label for="due_date" class="field-label">Requested repayment date</label><input id="due_date" name="due_date" type="date" min="{{ today()->addDay()->format('Y-m-d') }}" value="{{ old('due_date', today()->addMonths(3)->format('Y-m-d')) }}" required class="field-input"></div>
        <div class="rounded-xl bg-green-50 p-4 text-sm text-green-900"><strong>Before you submit:</strong> your request will be visible to the Treasurer. The loan is not active until approved.</div>
        <div class="flex justify-end"><button type="submit" class="btn-primary">Submit loan request</button></div>
    </form>
</div>
@endsection
