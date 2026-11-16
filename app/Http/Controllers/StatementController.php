<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class StatementController extends Controller
{
    public function __invoke(): Response
    {
        $member = auth()->user()->member;
        abort_unless($member, 404);

        $member->load([
            'user',
            'chama',
            'contributions' => fn ($query) => $query->where('status', 'confirmed')->orderBy('payment_date'),
            'loans' => fn ($query) => $query->with(['repayments' => fn ($repayments) => $repayments
                ->where('status', 'confirmed')->orderBy('date')])->orderBy('created_at'),
        ]);

        $totalContributions = $member->contributions->sum('amount');
        $loanBalance = $member->loans->where('status', 'approved')->sum('balance');

        return Pdf::loadView('statements.member', compact('member', 'totalContributions', 'loanBalance'))
            ->setPaper('a4')
            ->download('statement-'.$member->id.'-'.now()->format('Y-m-d').'.pdf');
    }
}
