<?php

namespace App\Http\Controllers;

use App\Models\Chama;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChamaController extends Controller
{
    public function create(): View|RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        if (auth()->user()->administeredChamas()->exists()) {
            return redirect()->route('dashboard');
        }

        return view('chama.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_if($request->user()->administeredChamas()->exists(), 403, 'Your account already has a Chama.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'monthly_amount' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'meeting_day' => ['required', 'integer', 'between:1,31'],
        ]);

        Chama::create([...$validated, 'admin_user_id' => $request->user()->id]);

        return redirect()->route('dashboard')->with('success', 'Chama created. You can now add members.');
    }
}
