@extends('layouts.dashboard')
@section('title', $sale->reference)
@section('content')
@include('partials.page-header', ['title' => 'Sale Invoice '.$sale->reference])
<div class="bg-white rounded-xl border overflow-hidden shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Product</th><th class="px-4 py-3 text-right">Qty</th><th class="px-4 py-3 text-right">Price</th><th class="px-4 py-3 text-right">Subtotal</th></tr></thead><tbody>@foreach($sale->items as $i)<tr class="border-t"><td class="px-4 py-3">{{ $i->product_name }}</td><td class="px-4 py-3 text-right">{{ $i->quantity }}</td><td class="px-4 py-3 text-right">{{ number_format($i->unit_price,2) }}</td><td class="px-4 py-3 text-right">{{ number_format($i->subtotal,2) }}</td></tr>@endforeach</tbody><tfoot><tr class="border-t font-semibold"><td colspan="3" class="px-4 py-3 text-right">Total</td><td class="px-4 py-3 text-right">{{ number_format($sale->total,2) }}</td></tr></tfoot></table></div>
@endsection
