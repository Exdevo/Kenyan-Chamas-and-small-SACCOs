@extends('layouts.app')

@section('title', 'Loans')

@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div><h1 class="text-2xl font-bold text-slate-900">Loans</h1><p class="mt-1 text-sm text-slate-500">{{ $chama->name }} · requests, approvals and repayments</p></div>
    @if (auth()->user()->role === 'member')<a href="{{ route('loans.create') }}" class="btn-primary">+ Request a loan</a>@endif
</div>
<div class="space-y-4">
    @forelse ($loans as $loan)
        <article class="card">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2"><h2 class="font-bold text-slate-900">@if (auth()->user()->role !== 'member'){{ $loan->member->user->name }} · @endif KSh {{ number_format((float) $loan->amount, 2) }}</h2><span class="badge badge-{{ $loan->status }}">{{ $loan->status }}</span></div>
                    <p class="mt-1 text-sm text-slate-500">{{ $loan->reason }}</p>
                </div>
                <div class="text-left sm:text-right"><p class="text-xs text-slate-500">Total with 10% flat interest</p><p class="font-semibold text-slate-900">KSh {{ number_format((float) $loan->total_to_pay, 2) }}</p></div>
            </div>
            <div class="mt-5 grid gap-4 border-t border-slate-100 pt-4 text-sm sm:grid-cols-4">
                <div><p class="text-xs text-slate-500">Guarantor</p><p class="mt-1 font-medium">{{ $loan->guarantor->user->name }}</p></div>
                <div><p class="text-xs text-slate-500">Due date</p><p class="mt-1 font-medium">{{ $loan->due_date->format('d M Y') }}</p></div>
                <div><p class="text-xs text-slate-500">Outstanding</p><p class="mt-1 font-semibold">KSh {{ number_format((float) $loan->balance, 2) }}</p></div>
                <div><p class="text-xs text-slate-500">Repayments</p><p class="mt-1 font-medium">{{ $loan->repayments->where('status', 'confirmed')->count() }} confirmed</p></div>
            </div>

            @if (auth()->user()->isTreasurer() && $loan->status === 'pending')
                <div class="mt-4 flex gap-2 border-t border-slate-100 pt-4">
                    <form method="POST" action="{{ route('loans.approve', $loan) }}">@csrf<button type="submit" class="btn-primary">Approve · KSh {{ number_format((float) $loan->total_to_pay, 2) }}</button></form>
                    <form method="POST" action="{{ route('loans.reject', $loan) }}">@csrf<button type="submit" class="btn-secondary">Reject</button></form>
                </div>
            @endif

            @if (auth()->user()->role === 'member' && $loan->status === 'approved' && (float) $loan->balance > 0)
                <form method="POST" action="{{ route('loans.repay', $loan) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-[1fr_1fr_1.2fr_auto] sm:items-end">
                    @csrf
                    <div><label for="amount-{{ $loan->id }}" class="field-label">Repayment (KSh)</label><input id="amount-{{ $loan->id }}" name="amount" type="number" min="0.01" max="{{ $loan->balance }}" step="0.01" required class="field-input"></div>
                    <div><label for="date-{{ $loan->id }}" class="field-label">Date paid</label><input id="date-{{ $loan->id }}" name="date" type="date" max="{{ today()->format('Y-m-d') }}" value="{{ today()->format('Y-m-d') }}" required class="field-input"></div>
                    <div><label for="code-{{ $loan->id }}" class="field-label">M-Pesa code (optional)</label><input id="code-{{ $loan->id }}" name="mpesa_code" maxlength="20" class="field-input"></div>
                    <button type="submit" class="btn-primary">Record repayment</button>
                </form>
                <p class="mt-2 text-xs text-slate-500">The loan balance updates once the Treasurer confirms your repayment.</p>
            @endif

            @if ($loan->repayments->isNotEmpty())
                <details class="mt-4 border-t border-slate-100 pt-4">
                    <summary class="cursor-pointer text-sm font-semibold text-green-800">Repayment history</summary>
                    <div class="mt-3 overflow-x-auto"><table class="w-full min-w-[480px] text-left text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="pb-2">Date</th><th class="pb-2">M-Pesa code</th><th class="pb-2 text-right">Amount</th><th class="pb-2 pl-4">Status</th>@if (auth()->user()->isTreasurer())<th class="pb-2 pl-4">Action</th>@endif</tr></thead><tbody class="divide-y divide-slate-100">
                    @foreach ($loan->repayments->sortByDesc('date') as $repayment)
                        <tr><td class="py-2">{{ $repayment->date->format('d M Y') }}</td><td class="py-2">{{ $repayment->mpesa_code ?: '—' }}</td><td class="py-2 text-right">KSh {{ number_format((float) $repayment->amount, 2) }}</td><td class="py-2 pl-4"><span class="badge badge-{{ $repayment->status }}">{{ $repayment->status }}</span></td>
                        @if (auth()->user()->isTreasurer())<td class="py-2 pl-4">@if ($repayment->status === 'pending')<form method="POST" action="{{ route('repayments.confirm', $repayment) }}">@csrf<button type="submit" class="text-xs font-semibold text-green-700 hover:text-green-900">Confirm</button></form>@else<span class="text-xs text-slate-500">Locked</span>@endif</td>@endif
                        </tr>
                    @endforeach
                    </tbody></table></div>
                </details>
            @endif
        </article>
    @empty
        <section class="card py-12 text-center"><h2 class="font-semibold text-slate-900">No loans yet</h2><p class="mt-1 text-sm text-slate-500">Loan requests and their balances will appear here.</p></section>
    @endforelse
</div>
<div class="mt-5">{{ $loans->links() }}</div>
@endsection
