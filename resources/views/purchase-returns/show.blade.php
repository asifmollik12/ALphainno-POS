@extends('layouts.dashboard')

@section('title', $return->reference)

@section('content')
@php $fmt = fn ($n) => $currency . number_format($n, 2); @endphp

<div class="mb-5 flex flex-wrap items-center justify-between gap-4">
    <div>
        <a href="{{ route('purchase-returns.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to list
        </a>
        <h1 class="text-xl font-bold text-slate-900">{{ $return->reference }}</h1>
        <p class="text-slate-500 text-sm mt-1">{{ $return->return_date->format('M d, Y') }}</p>
    </div>
    <button type="button" onclick="window.print()" class="px-5 py-2 rounded-lg bg-ai-purple hover:bg-violet-600 text-white text-sm font-medium">Print</button>
</div>

<div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
    <div class="xl:col-span-8 space-y-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-200 text-sm font-semibold text-slate-800">Returned products</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-ai-mist text-slate-600 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Product</th>
                            <th class="px-4 py-3 text-right">Quantity</th>
                            <th class="px-4 py-3 text-right">Unit cost</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($return->items as $item)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $item->product_name }}</td>
                            <td class="px-4 py-3 text-right">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-right">{{ $fmt($item->unit_cost) }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ $fmt($item->subtotal) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right font-semibold text-slate-700">Total</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900">{{ $fmt($return->total) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="xl:col-span-4 space-y-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-3 text-sm">
            <h2 class="font-semibold text-slate-900">Details</h2>
            <div class="flex justify-between gap-4">
                <span class="text-slate-500">Return ID</span>
                <span class="font-medium text-slate-900">{{ $return->reference }}</span>
            </div>
            <div class="flex justify-between gap-4">
                <span class="text-slate-500">Date</span>
                <span>{{ $return->return_date->format('M d, Y') }}</span>
            </div>
            <div class="flex justify-between gap-4">
                <span class="text-slate-500">Supplier</span>
                <span>{{ $return->supplier->name ?? '—' }}</span>
            </div>
            <div class="flex justify-between gap-4">
                <span class="text-slate-500">Purchase invoice</span>
                @if ($return->purchase)
                    <a href="{{ route('purchases.show', $return->purchase) }}" class="text-ai-cyan hover:underline font-medium">{{ $return->purchase->reference }}</a>
                @else
                    <span>—</span>
                @endif
            </div>
            <div>
                <span class="text-slate-500 block mb-1">Notes</span>
                <p class="text-slate-700">{{ $return->notes ?: '—' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
