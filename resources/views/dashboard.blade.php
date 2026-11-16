@extends('layouts.app')

@section('title', 'Overview')

@section('content')
<div class="mb-7 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-medium text-green-700">{{ now()->format('l, j F Y') }}</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
            @if (auth()->user()->role === 'member') Welcome, {{ auth()->user()->name }} @else {{ $chama?->name ?? 'Your group starts here' }} @endif
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            @if ($chama) Your group’s savings and lending at a glance. @else Set up your group to begin tracking contributions and loans. @endif
        </p>
    </div>
    @if (auth()->user()->role === 'admin' && ! $chama)
        <a href="{{ route('chama.create') }}" class="btn-primary">Set up a Chama</a>
    @elseif (auth()->user()->role === 'admin' && $chama)
        <a href="{{ route('members.create') }}" class="btn-primary">+ Add member</a>
    @elseif (auth()->user()->role === 'member')
        <a href="{{ route('statement.download') }}" class="btn-secondary">↓ Download statement</a>
    @endif
</div>

@if (! $chama)
    <section class="card max-w-2xl">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-green-50 text-2xl text-green-700">♧</div>
        <h2 class="mt-4 text-lg font-bold text-slate-900">Everything your group needs, in one place</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Create your Chama, set the monthly contribution, and invite members with their own secure sign-ins.</p>
        <a href="{{ route('chama.create') }}" class="btn-primary mt-5">Create your Chama</a>
    </section>
@elseif (auth()->user()->role === 'member')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <section class="card"><p class="text-sm font-medium text-slate-500">My contributions</p><p class="mt-3 text-3xl font-bold text-slate-900">KSh {{ number_format((float) $myContributions, 2) }}</p><p class="mt-2 text-xs text-slate-500">Confirmed payments</p></section>
        <section class="card"><p class="text-sm font-medium text-slate-500">My loan balance</p><p class="mt-3 text-3xl font-bold text-slate-900">KSh {{ number_format((float) $myLoanBalance, 2) }}</p><p class="mt-2 text-xs text-slate-500">Across active loans</p></section>
        <section class="card"><p class="text-sm font-medium text-slate-500">Monthly contribution</p><p class="mt-3 text-3xl font-bold text-slate-900">KSh {{ number_format((float) $chama->monthly_amount, 2) }}</p><p class="mt-2 text-xs text-slate-500">{{ $chama->name }}</p></section>
    </div>

    <section class="card mt-6">
        <div class="mb-5 flex items-center justify-between gap-3">
            <div><h2 class="font-bold text-slate-900">My recent payments</h2><p class="mt-1 text-sm text-slate-500">Your latest contribution records.</p></div>
            <a href="{{ route('contributions.index') }}" class="text-sm font-semibold text-green-700 hover:text-green-800">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-left text-sm">
                <thead class="text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-3 font-semibold">Date</th><th class="pb-3 font-semibold">Month</th><th class="pb-3 font-semibold">Method</th><th class="pb-3 text-right font-semibold">Amount</th><th class="pb-3 pl-4 font-semibold">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentPayments as $payment)
                        <tr><td class="py-3">{{ $payment->payment_date->format('d M Y') }}</td><td class="py-3">{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $payment->month)->format('M Y') }}</td><td class="py-3 capitalize">{{ $payment->payment_method }}</td><td class="py-3 text-right font-semibold">KSh {{ number_format((float) $payment->amount, 2) }}</td><td class="py-3 pl-4"><span class="badge badge-{{ $payment->status }}">{{ $payment->status }}</span></td></tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">No payments have been recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@else
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <section class="card"><div class="flex items-center justify-between"><p class="text-sm font-medium text-slate-500">Active members</p><span class="text-xl text-green-700">♧</span></div><p class="mt-4 text-3xl font-bold text-slate-900">{{ number_format($totalMembers) }}</p><p class="mt-1 text-xs text-slate-500">In your Chama</p></section>
        <section class="card"><div class="flex items-center justify-between"><p class="text-sm font-medium text-slate-500">Collected this month</p><span class="text-xl text-green-700">↗</span></div><p class="mt-4 text-2xl font-bold text-slate-900">KSh {{ number_format((float) $collectedThisMonth, 2) }}</p><p class="mt-1 text-xs text-slate-500">{{ now()->format('F Y') }}</p></section>
        <section class="card"><div class="flex items-center justify-between"><p class="text-sm font-medium text-slate-500">Loans given</p><span class="text-xl text-green-700">▤</span></div><p class="mt-4 text-2xl font-bold text-slate-900">KSh {{ number_format((float) $totalLoansGiven, 2) }}</p><p class="mt-1 text-xs text-slate-500">Approved and paid loans</p></section>
        <section class="card"><div class="flex items-center justify-between"><p class="text-sm font-medium text-slate-500">Outstanding loans</p><span class="text-xl text-amber-600">◷</span></div><p class="mt-4 text-2xl font-bold text-slate-900">KSh {{ number_format((float) $outstandingLoans, 2) }}</p><p class="mt-1 text-xs text-slate-500">Balance to be repaid</p></section>
    </div>

    <section class="card mt-6">
        <div class="mb-5 flex items-center justify-between gap-3">
            <div><h2 class="font-bold text-slate-900">Overdue loans</h2><p class="mt-1 text-sm text-slate-500">Approved loans past their due date with a balance remaining.</p></div>
            <a href="{{ route('loans.index') }}" class="text-sm font-semibold text-green-700 hover:text-green-800">View loans</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead class="text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-3 font-semibold">Member</th><th class="pb-3 font-semibold">Due date</th><th class="pb-3 text-right font-semibold">Outstanding</th><th class="pb-3 pl-4 font-semibold">Days overdue</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($defaulters as $loan)
                        <tr><td class="py-3 font-medium">{{ $loan->member->user->name }}</td><td class="py-3">{{ $loan->due_date->format('d M Y') }}</td><td class="py-3 text-right font-semibold">KSh {{ number_format((float) $loan->balance, 2) }}</td><td class="py-3 pl-4"><span class="badge badge-rejected">{{ $loan->due_date->diffInDays(today()) }} days</span></td></tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-500">No overdue balances. Great work keeping things on track.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endif
@endsection
