@extends('layouts.dashboard')
@section('title', 'Transactions')
@section('content')
@include('partials.page-header', ['title' => 'Transactions', 'actionUrl' => route('transactions.create'), 'actionLabel' => '+ Create Transaction'])
<div class="bg-white rounded-xl border overflow-hidden shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Account</th><th class="px-4 py-3 text-left">Description</th><th class="px-4 py-3 text-right">Type</th><th class="px-4 py-3 text-right">Amount</th></tr></thead><tbody>@forelse($transactions as $t)<tr class="border-t"><td class="px-4 py-3">{{ $t->transaction_date->format('M d, Y') }}</td><td class="px-4 py-3">{{ $t->account->name ?? '—' }}</td><td class="px-4 py-3">{{ $t->description ?? $t->reference ?? '—' }}</td><td class="px-4 py-3 text-right uppercase text-xs">{{ $t->type }}</td><td class="px-4 py-3 text-right font-medium">{{ number_format($t->amount,2) }}</td></tr>@empty<tr><td colspan="5" class="py-12 text-center text-slate-400">No transactions.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $transactions->links() }}</div>
@endsection
