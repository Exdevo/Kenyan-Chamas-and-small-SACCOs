@extends('layouts.app')

@section('title', 'Members')

@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div><h1 class="text-2xl font-bold text-slate-900">Members</h1><p class="mt-1 text-sm text-slate-500">{{ $chama->name }} · {{ $members->total() }} members</p></div>
    @if (auth()->user()->isAdmin())<a href="{{ route('members.create') }}" class="btn-primary">+ Add member</a>@endif
</div>
<section class="card">
    <form method="GET" action="{{ route('members.index') }}" class="mb-5 flex flex-wrap gap-3">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, phone or ID number" class="field-input max-w-md">
        <button class="btn-secondary" type="submit">Search</button>
        @if (request('search'))<a href="{{ route('members.index') }}" class="btn-secondary">Clear</a>@endif
    </form>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[680px] text-left text-sm">
            <thead class="text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-3 font-semibold">Member</th><th class="pb-3 font-semibold">Phone</th><th class="pb-3 font-semibold">ID number</th><th class="pb-3 font-semibold">Joined</th><th class="pb-3 font-semibold">Status</th><th class="pb-3 font-semibold">Role</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($members as $member)
                    <tr><td class="py-3 font-semibold text-slate-900">{{ $member->user->name }}<span class="block text-xs font-normal text-slate-500">{{ $member->user->email }}</span></td><td class="py-3">{{ $member->user->phone ?: '—' }}</td><td class="py-3">{{ $member->id_number }}</td><td class="py-3">{{ $member->joined_date->format('d M Y') }}</td><td class="py-3"><span class="badge badge-{{ $member->status }}">{{ $member->status }}</span></td><td class="py-3 capitalize">{{ $member->user->role }}</td></tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-slate-500">No members found. Add your first member to get started.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $members->links() }}</div>
</section>
@endsection
