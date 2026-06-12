@extends('layouts.dashboard')
@section('title', 'Create Transaction')
@section('content')
@include('partials.page-header', ['title' => 'Create Transaction'])
<form method="POST" action="{{ route('transactions.store') }}" class="bg-white rounded-xl border p-6 max-w-xl space-y-4">@csrf
<div><label class="text-sm font-medium">Account</label><select name="account_id" required class="w-full mt-1 rounded-lg border-slate-300">@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
<div><label class="text-sm font-medium">Type</label><select name="type" class="w-full mt-1 rounded-lg border-slate-300"><option value="credit">Credit</option><option value="debit">Debit</option></select></div>
<div><label class="text-sm font-medium">Amount</label><input type="number" step="0.01" name="amount" required class="w-full mt-1 rounded-lg border-slate-300"></div>
<div><label class="text-sm font-medium">Date</label><input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required class="w-full mt-1 rounded-lg border-slate-300"></div>
<div><label class="text-sm font-medium">Description</label><input name="description" class="w-full mt-1 rounded-lg border-slate-300"></div>
<button class="px-5 py-2 bg-violet-600 text-white rounded-lg">Save</button></form>
@endsection
