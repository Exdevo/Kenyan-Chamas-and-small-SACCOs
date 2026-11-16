<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContributionController extends Controller
{
    public function index(Request $request): View
    {
        $chama = $request->user()->chama();
        abort_unless($chama, 404);

        $contributions = Contribution::with('member.user')
            ->whereHas('member', fn ($query) => $query->where('chama_id', $chama->id))
            ->when($request->user()->role === 'member', fn ($query) => $query
                ->where('member_id', $request->user()->member?->id))
            ->latest('payment_date')
            ->paginate(20);

        return view('contributions.index', compact('chama', 'contributions'));
    }

    public function create(Request $request): View
    {
        abort_unless(in_array($request->user()->role, ['admin', 'treasurer'], true), 403);
        $chama = $request->user()->chama();
        abort_unless($chama, 404);
        $members = $chama->members()->where('status', 'active')->with('user')->get();

        return view('contributions.create', compact('chama', 'members'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'treasurer'], true), 403);
        $chama = $request->user()->chama();
        abort_unless($chama, 404);

        $validated = $request->validate([
            'member_id' => ['required', Rule::exists('members', 'id')->where('chama_id', $chama->id)->where('status', 'active')],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'month' => ['required', 'date_format:Y-m'],
            'payment_method' => ['required', Rule::in(['mpesa', 'cash', 'bank'])],
            'mpesa_code' => ['nullable', 'required_if:payment_method,mpesa', 'string', 'max:20', 'unique:contributions,mpesa_code'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        Contribution::create([...$validated, 'status' => 'pending']);

        return redirect()->route('contributions.index')->with('success', 'Payment recorded and awaiting Treasurer confirmation.');
    }

    public function confirm(Request $request, Contribution $contribution): RedirectResponse
    {
        abort_unless($request->user()->isTreasurer(), 403);
        $chama = $request->user()->chama();
        abort_unless($chama?->treasurer_user_id === $request->user()->id, 403);
        abort_unless($chama && $contribution->member->chama_id === $chama->id, 404);
        abort_if($contribution->status !== 'pending', 422, 'Only pending payments can be confirmed.');

        DB::transaction(function () use ($request, $contribution) {
            $locked = Contribution::query()->lockForUpdate()->findOrFail($contribution->id);
            abort_if($locked->status !== 'pending', 422, 'This payment has already been processed.');
            $locked->update([
                'status' => 'confirmed',
                'confirmed_by' => $request->user()->id,
                'confirmed_at' => now(),
            ]);
        });

        return back()->with('success', 'Payment confirmed and added to the ledger.');
    }
}
