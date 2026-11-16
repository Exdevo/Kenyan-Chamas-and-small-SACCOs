<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanRepayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $chama = $request->user()->chama();
        abort_unless($chama, 404);

        $loans = Loan::with(['member.user', 'guarantor.user', 'repayments'])
            ->whereHas('member', fn ($query) => $query->where('chama_id', $chama->id))
            ->when($request->user()->role === 'member', fn ($query) => $query
                ->where('member_id', $request->user()->member?->id))
            ->latest()
            ->paginate(15);

        $repayableLoans = $request->user()->member?->loans()
            ->where('status', 'approved')->where('balance', '>', 0)->get() ?? collect();

        return view('loans.index', compact('chama', 'loans', 'repayableLoans'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->role === 'member', 403);
        $member = $request->user()->member;
        abort_unless($member && $member->status === 'active', 403);
        $guarantors = $member->chama->members()
            ->where('status', 'active')->where('id', '<>', $member->id)->with('user')->get();

        return view('loans.create', compact('member', 'guarantors'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'member', 403);
        $member = $request->user()->member;
        abort_unless($member && $member->status === 'active', 403);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'reason' => ['required', 'string', 'max:2000'],
            'guarantor_id' => [
                'required',
                Rule::exists('members', 'id')->where('chama_id', $member->chama_id)->where('status', 'active'),
                Rule::notIn([$member->id]),
            ],
            'due_date' => ['required', 'date', 'after:today'],
        ]);

        $amount = round((float) $validated['amount'], 2);
        Loan::create([
            'member_id' => $member->id,
            'guarantor_id' => $validated['guarantor_id'],
            'amount' => $amount,
            'interest_rate' => 10,
            'total_to_pay' => round($amount * 1.10, 2),
            'balance' => 0,
            'reason' => $validated['reason'],
            'status' => 'pending',
            'due_date' => $validated['due_date'],
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan request submitted for Treasurer review.');
    }

    public function approve(Request $request, Loan $loan): RedirectResponse
    {
        abort_unless($request->user()->isTreasurer(), 403);
        $this->ensureLoanBelongsToTreasurer($request, $loan);

        DB::transaction(function () use ($request, $loan) {
            $locked = Loan::query()->lockForUpdate()->findOrFail($loan->id);
            abort_if($locked->status !== 'pending', 422, 'Only pending loan requests can be reviewed.');
            $locked->update([
                'status' => 'approved',
                'balance' => $locked->total_to_pay,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('success', 'Loan approved. The 10% flat interest is included in the balance.');
    }

    public function reject(Request $request, Loan $loan): RedirectResponse
    {
        abort_unless($request->user()->isTreasurer(), 403);
        $this->ensureLoanBelongsToTreasurer($request, $loan);

        DB::transaction(function () use ($request, $loan) {
            $locked = Loan::query()->lockForUpdate()->findOrFail($loan->id);
            abort_if($locked->status !== 'pending', 422, 'Only pending loan requests can be reviewed.');
            $locked->update([
                'status' => 'rejected',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('success', 'Loan request rejected.');
    }

    public function repay(Request $request, Loan $loan): RedirectResponse
    {
        $member = $request->user()->member;
        abort_unless($request->user()->role === 'member' && $member && $loan->member_id === $member->id, 403);
        abort_unless($loan->status === 'approved' && (float) $loan->balance > 0, 422, 'This loan is not accepting repayments.');

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'mpesa_code' => ['nullable', 'string', 'max:20', 'unique:loan_repayments,mpesa_code'],
        ]);

        LoanRepayment::create([...$validated, 'loan_id' => $loan->id, 'status' => 'pending']);

        return back()->with('success', 'Repayment recorded and awaiting Treasurer confirmation.');
    }

    public function confirmRepayment(Request $request, LoanRepayment $repayment): RedirectResponse
    {
        abort_unless($request->user()->isTreasurer(), 403);
        $loan = $repayment->loan;
        $this->ensureLoanBelongsToTreasurer($request, $loan);
        abort_if($repayment->status !== 'pending', 422, 'Only pending repayments can be confirmed.');

        DB::transaction(function () use ($request, $repayment) {
            $lockedRepayment = LoanRepayment::query()->lockForUpdate()->findOrFail($repayment->id);
            abort_if($lockedRepayment->status !== 'pending', 422, 'This repayment has already been processed.');

            $loan = Loan::query()->lockForUpdate()->findOrFail($lockedRepayment->loan_id);
            abort_unless($loan->status === 'approved', 422, 'Only an approved loan can receive repayments.');
            abort_if((float) $lockedRepayment->amount > (float) $loan->balance, 422, 'Repayment exceeds the outstanding loan balance.');

            $newBalance = round((float) $loan->balance - (float) $lockedRepayment->amount, 2);
            $lockedRepayment->update([
                'status' => 'confirmed',
                'confirmed_by' => $request->user()->id,
                'confirmed_at' => now(),
            ]);
            $loan->update([
                'balance' => $newBalance,
                'status' => $newBalance === 0.0 ? 'paid' : 'approved',
            ]);
        });

        return back()->with('success', 'Repayment confirmed and loan balance updated.');
    }

    private function ensureLoanBelongsToTreasurer(Request $request, Loan $loan): void
    {
        $chama = $request->user()->chama();
        abort_unless($chama?->treasurer_user_id === $request->user()->id, 403);
        abort_unless($chama && $loan->member->chama_id === $chama->id, 404);
    }
}
