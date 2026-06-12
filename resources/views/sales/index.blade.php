@extends('layouts.dashboard')
@section('title', 'Sale Invoices')
@section('content')
@include('partials.page-header', ['title' => 'Sale Invoice', 'actionUrl' => route('pos.index'), 'actionLabel' => 'Open POS'])
<div class="bg-white rounded-xl border overflow-hidden shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-4 py-3 text-left">Reference</th><th class="px-4 py-3 text-left">Customer</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3 text-right">Paid</th><th class="px-4 py-3 text-right">Due</th><th class="px-4 py-3 text-right">Status</th></tr></thead>
<tbody>@forelse($sales as $s)<tr class="border-t hover:bg-slate-50"><td class="px-4 py-3"><a href="{{ route('sales.show',$s) }}" class="text-blue-600 font-medium">{{ $s->reference }}</a></td><td class="px-4 py-3">{{ $s->customer->name ?? 'Walk-in' }}</td><td class="px-4 py-3">{{ optional($s->sale_date)->format('M d, Y') ?? $s->created_at->format('M d, Y') }}</td><td class="px-4 py-3 text-right">{{ number_format($s->total,2) }}</td><td class="px-4 py-3 text-right">{{ number_format($s->paid_amount,2) }}</td><td class="px-4 py-3 text-right">{{ number_format($s->due_amount,2) }}</td><td class="px-4 py-3 text-right"><span class="text-xs px-2 py-0.5 bg-slate-100 rounded uppercase">{{ $s->payment_status }}</span></td></tr>@empty<tr><td colspan="7" class="py-12 text-center text-slate-400">No sales yet.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $sales->links() }}</div>
@endsection
