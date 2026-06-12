@extends('layouts.dashboard')
@section('title', 'Inventory Report')
@section('content')
@include('partials.page-header', ['title' => 'Inventory Report'])
<div class="bg-white rounded-xl border overflow-hidden shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Product</th><th class="px-4 py-3 text-right">Stock</th><th class="px-4 py-3 text-right">Cost</th><th class="px-4 py-3 text-right">Price</th><th class="px-4 py-3 text-right">Value</th></tr></thead><tbody>@foreach($products as $p)<tr class="border-t"><td class="px-4 py-3">{{ $p->name }}</td><td class="px-4 py-3 text-right">{{ $p->stock }}</td><td class="px-4 py-3 text-right">{{ number_format($p->cost_price,2) }}</td><td class="px-4 py-3 text-right">{{ number_format($p->price,2) }}</td><td class="px-4 py-3 text-right">{{ number_format($p->stock * $p->price,2) }}</td></tr>@endforeach</tbody></table></div>
@endsection
