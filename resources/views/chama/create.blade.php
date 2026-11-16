@extends('layouts.app')

@section('title', 'Set up your Chama')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-6">
        <p class="text-sm font-semibold text-green-700">WELCOME TO CHAMA LEDGER</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Set up your Chama</h1>
        <p class="mt-2 text-slate-600">Add a few details to start keeping your group’s records in one place.</p>
    </div>
    <form method="POST" action="{{ route('chama.store') }}" class="card space-y-5">
        @csrf
        <div>
            <label for="name" class="field-label">Chama name</label>
            <input id="name" name="name" value="{{ old('name') }}" required maxlength="120" placeholder="e.g. Mumias Unity Chama" class="field-input">
        </div>
        <div>
            <label for="monthly_amount" class="field-label">Monthly contribution (KSh)</label>
            <input id="monthly_amount" name="monthly_amount" type="number" step="0.01" min="1" value="{{ old('monthly_amount') }}" required class="field-input">
        </div>
        <div>
            <label for="meeting_day" class="field-label">Meeting day of the month</label>
            <select id="meeting_day" name="meeting_day" required class="field-input">
                @for ($day = 1; $day <= 31; $day++)
                    <option value="{{ $day }}" @selected(old('meeting_day', 1) == $day)>{{ $day }}{{ $day === 1 ? 'st' : ($day === 2 ? 'nd' : ($day === 3 ? 'rd' : 'th')) }}</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="btn-primary">Create Chama</button>
    </form>
</div>
@endsection
