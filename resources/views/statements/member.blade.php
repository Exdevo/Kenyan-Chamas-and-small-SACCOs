<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Member statement · {{ $member->user->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; }
        .header { border-bottom: 2px solid #15803d; padding-bottom: 14px; margin-bottom: 18px; }
        h1 { color: #14532d; font-size: 20px; margin: 0 0 4px; }
        h2 { color: #14532d; font-size: 14px; margin: 22px 0 8px; }
        p { margin: 4px 0; }
        table { border-collapse: collapse; width: 100%; margin-top: 8px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 8px 7px; text-align: left; }
        th { background: #f0fdf4; color: #166534; font-size: 9px; text-transform: uppercase; }
        .right { text-align: right; }
        .summary { margin-top: 20px; padding: 12px; background: #f0fdf4; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $member->chama->name }}</h1>
        <p class="muted">Member statement generated {{ now()->format('d M Y') }}</p>
        <p><strong>Member:</strong> {{ $member->user->name }} &nbsp; <strong>Phone:</strong> {{ $member->user->phone ?: '—' }} &nbsp; <strong>ID:</strong> {{ $member->id_number }}</p>
    </div>

    <h2>Confirmed contributions</h2>
    <table>
        <thead><tr><th>Date</th><th>Month</th><th>Payment method</th><th>M-Pesa code</th><th class="right">Amount (KSh)</th></tr></thead>
        <tbody>
            @forelse ($member->contributions as $contribution)
                <tr><td>{{ $contribution->payment_date->format('d M Y') }}</td><td>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $contribution->month)->format('M Y') }}</td><td>{{ ucfirst($contribution->payment_method) }}</td><td>{{ $contribution->mpesa_code ?: '—' }}</td><td class="right">{{ number_format((float) $contribution->amount, 2) }}</td></tr>
            @empty
                <tr><td colspan="5" class="muted">No confirmed contributions recorded.</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr><th colspan="4" class="right">Total contributions</th><th class="right">KSh {{ number_format((float) $totalContributions, 2) }}</th></tr></tfoot>
    </table>

    <h2>Loans and repayments</h2>
    <table>
        <thead><tr><th>Requested</th><th>Status</th><th>Loan amount</th><th>Total due</th><th>Balance</th><th>Due date</th></tr></thead>
        <tbody>
            @forelse ($member->loans as $loan)
                <tr><td>{{ $loan->created_at->format('d M Y') }}</td><td>{{ ucfirst($loan->status) }}</td><td>KSh {{ number_format((float) $loan->amount, 2) }}</td><td>KSh {{ number_format((float) $loan->total_to_pay, 2) }}</td><td>KSh {{ number_format((float) $loan->balance, 2) }}</td><td>{{ $loan->due_date->format('d M Y') }}</td></tr>
                @foreach ($loan->repayments as $repayment)
                    <tr><td colspan="2" class="muted">Repayment · {{ $repayment->date->format('d M Y') }} · {{ $repayment->mpesa_code ?: 'no code' }}</td><td colspan="4" class="right">KSh {{ number_format((float) $repayment->amount, 2) }}</td></tr>
                @endforeach
            @empty
                <tr><td colspan="6" class="muted">No loans recorded.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Total confirmed contributions:</strong> KSh {{ number_format((float) $totalContributions, 2) }}</p>
        <p><strong>Outstanding approved loan balance:</strong> KSh {{ number_format((float) $loanBalance, 2) }}</p>
        <p class="muted">Pending payments and requests are excluded from this statement.</p>
    </div>
</body>
</html>
