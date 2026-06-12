@extends('layouts.dashboard')
@section('title', 'Purchase Invoices')
@section('content')
@include('partials.page-header', ['title' => 'Purchase Invoice', 'actionUrl' => route('purchases.create'), 'actionLabel' => '+ Create Purchase'])
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
<table class="w-full text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-4 py-3 text-left">Reference</th><th class="px-4 py-3 text-left">Supplier</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3 text-right">Paid</th><th class="px-4 py-3 text-right">Due</th><th class="px-4 py-3 text-right">Status</th></tr></thead>
<tbody class="divide-y">@forelse($purchases as $p)<tr class="hover:bg-slate-50"><td class="px-4 py-3"><a href="{{ route('purchases.show',$p) }}" class="text-blue-600 font-medium">{{ $p->reference }}</a></td><td class="px-4 py-3">{{ $p->supplier->name ?? '—' }}</td><td class="px-4 py-3">{{ $p->purchase_date->format('M d, Y') }}</td><td class="px-4 py-3 text-right">{{ number_format($p->total,2) }}</td><td class="px-4 py-3 text-right">{{ number_format($p->paid_amount,2) }}</td><td class="px-4 py-3 text-right">{{ number_format($p->due_amount,2) }}</td><td class="px-4 py-3 text-right"><span class="px-2 py-0.5 rounded text-xs bg-slate-100">{{ strtoupper($p->payment_status) }}</span></td></tr>@empty<tr><td colspan="7" class="py-12 text-center text-slate-400">No purchases yet.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $purchases->links() }}</div>
@endsection
