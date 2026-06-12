@extends('layouts.dashboard')

@section('title', $purchase->reference)

@section('content')
@php
    $fmt = fn ($n) => $currency . number_format($n, 2);
    $statusClass = match ($purchase->payment_status) {
        'paid' => 'bg-emerald-100 text-emerald-700',
        'partial' => 'bg-amber-100 text-amber-700',
        default => 'bg-red-100 text-red-700',
    };
@endphp

<div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
    <div class="xl:col-span-8 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('purchases.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Back to invoices</a>
            <div class="flex gap-2">
                <button type="button" onclick="window.print()" class="px-4 py-2 rounded-lg bg-ai-navy text-white text-sm font-medium">Print</button>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ tab: 'products' }">
            <div class="flex border-b border-slate-200 text-sm">
                <button type="button" @click="tab = 'products'" :class="tab === 'products' ? 'border-b-2 border-ai-purple text-ai-purple font-semibold' : 'text-slate-500'" class="px-5 py-3">Products</button>
                <button type="button" @click="tab = 'returns'" :class="tab === 'returns' ? 'border-b-2 border-ai-purple text-ai-purple font-semibold' : 'text-slate-500'" class="px-5 py-3">Return products</button>
                <button type="button" @click="tab = 'transactions'" :class="tab === 'transactions' ? 'border-b-2 border-ai-purple text-ai-purple font-semibold' : 'text-slate-500'" class="px-5 py-3">Transactions</button>
            </div>

            <div x-show="tab === 'products'" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-ai-mist text-slate-600 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">ID</th>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-right">Quantity</th>
                            <th class="px-4 py-3 text-right">Price</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                            <th class="px-4 py-3 text-right">Tax Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($purchase->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-slate-500">{{ $item->product_id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $item->product_name }}</td>
                            <td class="px-4 py-3 text-right">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($item->subtotal, 2) }}</td>
                            <td class="px-4 py-3 text-right text-slate-500">0</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div x-show="tab === 'returns'" x-cloak class="p-8 text-center text-slate-400 text-sm">No return products for this invoice.</div>

            <div x-show="tab === 'transactions'" x-cloak class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-ai-mist text-slate-600 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($purchase->transactions as $txn)
                        <tr>
                            <td class="px-4 py-3">{{ $txn->transaction_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3">{{ $txn->description }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ $fmt($txn->amount) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">No payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="xl:col-span-4 space-y-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <div class="text-xs text-slate-500">Invoice No</div>
                    <div class="font-bold text-lg text-slate-900">#{{ $purchase->reference }}</div>
                </div>
                <span class="px-2.5 py-1 rounded text-xs font-bold {{ $statusClass }}">{{ $purchase->statusLabel() }}</span>
            </div>
            <div class="text-sm text-slate-500 mb-4">Invoice Date: {{ $purchase->purchase_date->format('M d, Y') }}</div>

            <dl class="space-y-2 text-sm border-t border-slate-100 pt-4">
                <div class="flex justify-between"><dt class="text-slate-500">Total Amount</dt><dd class="font-medium">{{ $fmt($purchase->total) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Total Tax</dt><dd>+ {{ $fmt(0) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Total Paid Amount</dt><dd class="text-emerald-600">- {{ $fmt($purchase->paid_amount) }}</dd></div>
                <div class="flex justify-between font-bold pt-2 border-t"><dt>Due Amount</dt><dd class="{{ $purchase->due_amount > 0 ? 'text-red-600' : '' }}">{{ $fmt($purchase->due_amount) }}</dd></div>
            </dl>

            @if ($purchase->due_amount > 0)
            <button type="button" @click="$dispatch('open-pay', { id: {{ $purchase->id }}, ref: @json($purchase->reference), due: {{ $purchase->due_amount }} })" class="mt-4 w-full py-2 rounded-lg border border-ai-purple text-ai-purple text-sm font-medium hover:bg-violet-50">Make a payment</button>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Supplier Details</h3>
            <dl class="space-y-2 text-sm">
                <div><dt class="text-slate-500">Name</dt><dd class="font-medium">{{ $purchase->supplier->name ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Phone</dt><dd>{{ $purchase->supplier->phone ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Address</dt><dd>{{ $purchase->supplier->address ?? '—' }}</dd></div>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h3 class="font-semibold text-slate-900 mb-2">Note</h3>
            <p class="text-sm text-slate-500">{{ $purchase->notes ?: 'no note available' }}</p>
        </div>
    </div>
</div>

@include('purchases._pay-modal', ['currency' => $currency])
@endsection
