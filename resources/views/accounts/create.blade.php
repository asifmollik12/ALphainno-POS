@extends('layouts.dashboard')
@section('title', 'Add Account')
@section('content')
@include('partials.page-header', ['title' => 'Add Account'])
<form method="POST" action="{{ route('accounts.store') }}" class="bg-white rounded-xl border p-6 max-w-xl space-y-4">@csrf
<div><label class="text-sm font-medium">Name</label><input name="name" required class="w-full mt-1 rounded-lg border-slate-300"></div>
<div><label class="text-sm font-medium">Code</label><input name="code" class="w-full mt-1 rounded-lg border-slate-300"></div>
<div><label class="text-sm font-medium">Type</label><select name="type" class="w-full mt-1 rounded-lg border-slate-300"><option value="asset">Asset</option><option value="liability">Liability</option><option value="equity">Equity</option><option value="income">Income</option><option value="expense">Expense</option></select></div>
<div><label class="text-sm font-medium">Opening balance</label><input type="number" step="0.01" name="opening_balance" value="0" class="w-full mt-1 rounded-lg border-slate-300"></div>
<button class="px-5 py-2 bg-violet-600 text-white rounded-lg">Create</button></form>
@endsection
