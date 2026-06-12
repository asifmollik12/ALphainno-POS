@extends('layouts.app')

@section('title', 'Sales History')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-semibold text-white">Sales history</h1>
    <p class="text-slate-400 text-sm mt-0.5">Recent completed sales</p>
</div>

@if ($sales->isEmpty())
    <div class="text-center py-16 border border-dashed border-slate-700 rounded-xl">
        <p class="text-slate-400">No sales yet.</p>
    </div>
@else
    <div class="space-y-4">
        @foreach ($sales as $sale)
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <div>
                    <span class="font-medium text-white">{{ $sale->reference }}</span>
                    <span class="text-slate-500 text-sm ml-2">{{ $sale->created_at->format('M d, Y h:i A') }}</span>
                </div>
                <span class="text-emerald-400 font-semibold">৳{{ number_format($sale->total, 2) }}</span>
            </div>
            <ul class="text-sm text-slate-400 space-y-1">
                @foreach ($sale->items as $item)
                <li>{{ $item->product_name }} — {{ $item->quantity }} × ৳{{ number_format($item->unit_price, 2) }}</li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $sales->links() }}</div>
@endif
@endsection
