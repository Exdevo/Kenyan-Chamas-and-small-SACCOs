<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $chama = $request->user()->chama();
        abort_unless($chama, 404, 'Create a Chama before adding members.');

        $members = $chama->members()
            ->with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search'));
                $query->where(function ($members) use ($search) {
                    $members->whereHas('user', fn ($users) => $users
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"))
                        ->orWhere('id_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('members.index', compact('chama', 'members'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($request->user()->chama(), 404, 'Create a Chama before adding members.');

        return view('members.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $chama = $request->user()->chama();
        abort_unless($chama, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'id_number' => ['required', 'string', 'max:30', 'unique:members,id_number,NULL,id,chama_id,'.$chama->id],
            'joined_date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'in:active,inactive'],
            'role' => ['required', 'in:member,treasurer'],
        ]);

        DB::transaction(function () use ($validated, $chama) {
            if ($validated['role'] === 'treasurer' && $chama->treasurer_user_id) {
                User::whereKey($chama->treasurer_user_id)->where('role', 'treasurer')->update(['role' => 'member']);
            }

            $user = User::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $validated['role'],
            ]);

            $chama->members()->create([
                'user_id' => $user->id,
                'id_number' => $validated['id_number'],
                'joined_date' => $validated['joined_date'],
                'status' => $validated['status'],
            ]);

            if ($validated['role'] === 'treasurer') {
                $chama->update(['treasurer_user_id' => $user->id]);
            }
        });

        return redirect()->route('members.index')->with('success', 'Member account created.');
    }
}
