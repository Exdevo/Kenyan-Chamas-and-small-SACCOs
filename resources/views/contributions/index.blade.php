@extends('layouts.app')

@section('title', 'Contributions')

@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div><h1 class="text-2xl font-bold text-slate-900">Contributions</h1><p class="mt-1 text-sm text-slate-500">{{ $chama->name }} · verified savings ledger</p></div>
    @if (in_array(auth()->user()->role, ['admin', 'treasurer'], true))<a href="{{ route('contributions.create') }}" class="btn-primary">+ Record payment</a>@endif
</div>
<section class="card">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[790px] text-left text-sm">
            <thead class="text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-3 font-semibold">Member</th><th class="pb-3 font-semibold">Month</th><th class="pb-3 font-semibold">Payment date</th><th class="pb-3 font-semibold">Method / code</th><th class="pb-3 text-right font-semibold">Amount</th><th class="pb-3 pl-4 font-semibold">Status</th>@if (auth()->user()->isTreasurer())<th class="pb-3 pl-4 font-semibold">Action</th>@endif</tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($contributions as $contribution)
                    <tr>
                        <td class="py-3 font-semibold">{{ $contribution->member->user->name }}</td>
                        <td class="py-3">{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $contribution->month)->format('M Y') }}</td>
                        <td class="py-3">{{ $contribution->payment_date->format('d M Y') }}</td>
                        <td class="py-3 capitalize">{{ $contribution->payment_method }}<span class="block text-xs text-slate-500">{{ $contribution->mpesa_code ?: '—' }}</span></td>
                        <td class="py-3 text-right font-semibold">KSh {{ number_format((float) $contribution->amount, 2) }}</td>
                        <td class="py-3 pl-4"><span class="badge badge-{{ $contribution->status }}">{{ $contribution->status }}</span></td>
                        @if (auth()->user()->isTreasurer())
                            <td class="py-3 pl-4">
                                @if ($contribution->status === 'pending')
                                    <form method="POST" action="{{ route('contributions.confirm', $contribution) }}">@csrf<button class="btn-primary px-3 py-2 text-xs" type="submit">Confirm</button></form>
                                @else
                                    <span class="text-xs text-slate-500">Locked · {{ $contribution->confirmed_at?->format('d M Y') }}</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->isTreasurer() ? 7 : 6 }}" class="py-10 text-center text-slate-500">No contributions recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $contributions->links() }}</div>
</section>
<p class="mt-4 text-xs leading-5 text-slate-500">Confirmed payments are permanent ledger records. Pending entries are not included in totals until verified by the Treasurer.</p>
@endsection
