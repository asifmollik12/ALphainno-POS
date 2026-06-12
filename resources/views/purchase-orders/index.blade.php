@extends('layouts.dashboard')
@section('title', 'Purchase Orders')
@section('content')
@include('partials.page-header', ['title' => 'Purchase Order', 'actionUrl' => route('purchase-orders.create'), 'actionLabel' => '+ New Order'])
<div class="bg-white rounded-xl border overflow-hidden shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Reference</th><th class="px-4 py-3 text-left">Supplier</th><th class="px-4 py-3 text-left">Order date</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3 text-right">Status</th></tr></thead><tbody>@forelse($orders as $o)<tr class="border-t"><td class="px-4 py-3 font-medium">{{ $o->reference }}</td><td class="px-4 py-3">{{ $o->supplier->name ?? '—' }}</td><td class="px-4 py-3">{{ $o->order_date->format('M d, Y') }}</td><td class="px-4 py-3 text-right">{{ number_format($o->total,2) }}</td><td class="px-4 py-3 text-right uppercase text-xs">{{ $o->status }}</td></tr>@empty<tr><td colspan="5" class="py-12 text-center text-slate-400">No orders.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
