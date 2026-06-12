@extends('layouts.dashboard')
@section('title', $purchase->reference)
@section('content')
@include('partials.page-header', ['title' => 'Purchase '.$purchase->reference, 'subtitle' => $purchase->purchase_date->format('M d, Y')])
<div class="grid lg:grid-cols-3 gap-4 mb-4">
    <div class="bg-white rounded-xl border p-4"><div class="text-slate-500 text-sm">Supplier</div><div class="font-semibold">{{ $purchase->supplier->name ?? '—' }}</div></div>
    <div class="bg-white rounded-xl border p-4"><div class="text-slate-500 text-sm">Total</div><div class="font-semibold">{{ number_format($purchase->total,2) }}</div></div>
    <div class="bg-white rounded-xl border p-4"><div class="text-slate-500 text-sm">Status</div><div class="font-semibold uppercase">{{ $purchase->payment_status }}</div></div>
</div>
<div class="bg-white rounded-xl border overflow-hidden shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Product</th><th class="px-4 py-3 text-right">Qty</th><th class="px-4 py-3 text-right">Cost</th><th class="px-4 py-3 text-right">Subtotal</th></tr></thead><tbody>@foreach($purchase->items as $i)<tr class="border-t"><td class="px-4 py-3">{{ $i->product_name }}</td><td class="px-4 py-3 text-right">{{ $i->quantity }}</td><td class="px-4 py-3 text-right">{{ number_format($i->unit_cost,2) }}</td><td class="px-4 py-3 text-right">{{ number_format($i->subtotal,2) }}</td></tr>@endforeach</tbody></table></div>
@endsection
