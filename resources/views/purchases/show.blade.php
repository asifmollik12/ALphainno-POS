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
    $gmailUrl = $purchase->gmailComposeUrl();
@endphp

<div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
    <div class="xl:col-span-8 space-y-4">
        <div class="flex flex-wrap items-center justify-end gap-2">
            <button type="button" onclick="window.print()" class="px-5 py-2 rounded-lg bg-ai-purple hover:bg-violet-600 text-white text-sm font-medium">Print</button>
            <a href="{{ $gmailUrl ?? '#' }}" target="_blank" rel="noopener" @class(['px-5 py-2 rounded-lg border border-slate-200 bg-white text-sm font-medium inline-flex items-center gap-2', 'pointer-events-none opacity-50' => ! $gmailUrl])>
                <svg class="w-4 h-4 text-ai-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Send
            </a>
            <x-row-actions-dropdown>
                <button type="button" @click="openPayModal({ id: {{ $purchase->id }}, ref: @json($purchase->reference), due: {{ $purchase->due_amount }} })" class="w-full flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-slate-700 text-left">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Make a payment
                </button>
                <a href="{{ $gmailUrl ?? '#' }}" target="_blank" rel="noopener" @class(['flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-slate-700', 'pointer-events-none opacity-50' => ! $gmailUrl]) @click="close()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Email
                </a>
                <button type="button" onclick="window.print()" class="w-full flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-slate-700 text-left">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </button>
            </x-row-actions-dropdown>
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
                            <td class="px-4 py-3 text-right">{{ number_format($item->unit_cost, 0) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($item->quantity * $item->unit_cost, 0) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($item->tax_amount ?? 0, 0) }}</td>
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
                <div class="flex justify-between"><dt class="text-slate-500">Total Tax</dt><dd>+ {{ number_format($purchase->tax_amount ?? 0, 0) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Total Paid Amount</dt><dd class="text-emerald-600">- {{ number_format($purchase->paid_amount, 2) }}</dd></div>
                <div class="flex justify-between font-bold pt-2 border-t"><dt>Due Amount</dt><dd class="{{ $purchase->due_amount > 0 ? 'text-red-600' : '' }}">{{ number_format($purchase->due_amount, 2) }}</dd></div>
            </dl>

            @if ($purchase->due_amount > 0)
            <button type="button" @click="window.openPurchasePayModal({ id: {{ $purchase->id }}, ref: @json($purchase->reference), due: {{ $purchase->due_amount }} })" class="mt-4 w-full py-2 rounded-lg border border-ai-purple text-ai-purple text-sm font-medium hover:bg-violet-50">Make a payment</button>
            @else
            <button type="button" @click="window.openPurchasePayModal({ id: {{ $purchase->id }}, ref: @json($purchase->reference), due: 0 })" class="mt-4 w-full py-2 rounded-lg border border-slate-200 text-slate-500 text-sm font-medium hover:bg-slate-50">Make a payment</button>
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

@endsection
