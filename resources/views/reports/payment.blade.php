@extends('layouts.dashboard')
@section('title', 'Payment Report')
@section('content')
@include('partials.page-header', ['title' => 'Payment Report'])
<form method="GET" class="mb-4 flex gap-2 items-center text-sm"><input type="date" name="from" value="{{ $from->toDateString() }}" class="rounded-lg border-slate-300"><span>to</span><input type="date" name="to" value="{{ $to->toDateString() }}" class="rounded-lg border-slate-300"><button class="px-3 py-1.5 bg-slate-900 text-white rounded-lg">Filter</button></form>
<div class="bg-white rounded-xl border overflow-hidden shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Account</th><th class="px-4 py-3 text-left">Description</th><th class="px-4 py-3 text-right">Amount</th></tr></thead><tbody>@forelse($transactions as $t)<tr class="border-t"><td class="px-4 py-3">{{ $t->transaction_date->format('M d, Y') }}</td><td class="px-4 py-3">{{ $t->account->name ?? '—' }}</td><td class="px-4 py-3">{{ $t->description ?? '—' }}</td><td class="px-4 py-3 text-right">{{ number_format($t->amount,2) }}</td></tr>@empty<tr><td colspan="4" class="py-12 text-center text-slate-400">No payments in range.</td></tr>@endforelse</tbody></table></div>
@endsection
