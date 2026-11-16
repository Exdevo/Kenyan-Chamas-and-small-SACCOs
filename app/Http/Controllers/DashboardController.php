<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $chama = $user->chama();

        if (! $chama && $user->isAdmin()) {
            return view('dashboard', ['chama' => null]);
        }

        abort_unless($chama, 403, 'Your account is not associated with a Chama yet.');

        if ($user->role === 'member') {
            $member = $user->member()->with('contributions')->firstOrFail();
            $loans = $member->loans()->with('repayments')->latest()->get();

            return view('dashboard', [
                'chama' => $chama,
                'member' => $member,
                'myContributions' => $member->contributions()->where('status', 'confirmed')->sum('amount'),
                'myLoanBalance' => $loans->whereIn('status', ['approved'])->sum('balance'),
                'recentPayments' => $member->contributions()->latest('payment_date')->limit(5)->get(),
                'loans' => $loans,
            ]);
        }

        $memberIds = $chama->members()->select('id');
        $month = Carbon::now()->format('Y-m');
        $totalMembers = $chama->members()->where('status', 'active')->count();
        $collectedThisMonth = Contribution::whereIn('member_id', $memberIds)
            ->where('status', 'confirmed')->where('month', $month)->sum('amount');
        $totalLoansGiven = Loan::whereIn('member_id', $memberIds)
            ->whereIn('status', ['approved', 'paid'])->sum('amount');
        $outstandingLoans = Loan::whereIn('member_id', $memberIds)
            ->where('status', 'approved')->sum('balance');
        $defaulters = Loan::with('member.user')
            ->whereIn('member_id', $memberIds)
            ->where('status', 'approved')
            ->where('balance', '>', 0)
            ->whereDate('due_date', '<', today())
            ->orderBy('due_date')
            ->get();

        return view('dashboard', compact(
            'chama', 'totalMembers', 'collectedThisMonth',
            'totalLoansGiven', 'outstandingLoans', 'defaulters'
        ));
    }
}
