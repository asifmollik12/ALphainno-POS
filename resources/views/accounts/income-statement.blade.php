@extends('layouts.dashboard')
@section('title', 'Income Statement')
@section('content')
@include('partials.page-header', ['title' => 'Income Statement'])
<div class="grid md:grid-cols-3 gap-4">
<div class="bg-white rounded-xl border p-5"><div class="text-slate-500 text-sm">Income</div><div class="text-2xl font-bold text-emerald-600">{{ number_format($income,2) }}</div></div>
<div class="bg-white rounded-xl border p-5"><div class="text-slate-500 text-sm">Expenses</div><div class="text-2xl font-bold text-red-600">{{ number_format($expense,2) }}</div></div>
<div class="bg-white rounded-xl border p-5"><div class="text-slate-500 text-sm">Net Profit</div><div class="text-2xl font-bold">{{ number_format($income - $expense,2) }}</div></div>
</div>
@endsection
