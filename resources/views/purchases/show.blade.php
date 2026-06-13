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
    $returnedValue = (float) $purchase->returned_amount;
    $refundedAmount = (float) ($purchase->refunded_amount ?? 0);
    $displayPaid = max(0, round((float) $purchase->paid_amount - $refundOwed, 2));
    $returnProductUrl = route('purchase-returns.create', ['purchase_id' => $purchase->id]);
@endphp

<div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
    <div class="xl:col-span-8 space-y-4">
        <div class="flex flex-wrap items-center justify-end gap-2">
            <button type="button" onclick="window.print()" class="px-5 py-2 rounded-lg bg-ai-navy hover:bg-slate-900 text-white text-sm font-medium">Print</button>
            <a href="{{ $gmailUrl ?? '#' }}" target="_blank" rel="noopener" @class(['px-5 py-2 rounded-lg border border-slate-200 bg-white text-sm font-medium inline-flex items-center gap-2 text-slate-700 hover:bg-slate-50', 'pointer-events-none opacity-50' => ! $gmailUrl])>
                <svg class="w-4 h-4 text-ai-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Send
            </a>
            <x-row-actions-dropdown>
                @include('purchases._action-make-payment', ['purchase' => $purchase])
                <a href="{{ $returnProductUrl }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-slate-700 text-sm" @click="close()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    Return Product
                </a>
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

            <div x-show="tab === 'returns'" x-cloak class="overflow-x-auto">
                @if ($purchase->purchaseReturns->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-sm">No return products for this invoice.</div>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-ai-mist text-slate-600 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Return ID</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Product</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($purchase->purchaseReturns as $return)
                                @foreach ($return->items as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('purchase-returns.show', $return) }}" class="text-ai-cyan hover:underline font-medium">{{ $return->reference }}</a>
                                    </td>
                                    <td class="px-4 py-3">{{ $return->return_date->format('M d, Y') }}</td>
                                    <td class="px-4 py-3">{{ $item->product_name }}</td>
                                    <td class="px-4 py-3 text-right">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right">{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

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
                            <td class="px-4 py-3 text-right font-medium {{ $txn->type === 'credit' ? 'text-emerald-600' : '' }}">
                                {{ $txn->type === 'credit' ? '+' : '-' }}{{ $fmt($txn->amount) }}
                            </td>
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
                <div class="flex justify-between"><dt class="text-slate-500">Total Amount</dt><dd class="font-medium">{{ number_format($purchase->total, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Total Tax</dt><dd>+ {{ number_format($purchase->tax_amount ?? 0, 0) }}</dd></div>
                @if ($returnedValue > 0)
                <div class="flex justify-between"><dt class="text-slate-500">Return Product Value</dt><dd class="text-red-600">- {{ number_format($returnedValue, 2) }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-slate-500">Return Amount</dt><dd>+ {{ number_format($refundedAmount, 0) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Total Paid Amount</dt><dd class="text-emerald-600">- {{ number_format($displayPaid, 2) }}</dd></div>
                <div class="flex justify-between font-bold pt-2 border-t"><dt>Due Amount</dt><dd class="{{ $purchase->due_amount > 0 ? 'text-red-600' : '' }}">{{ number_format($purchase->due_amount, 2) }}</dd></div>
            </dl>

            @if ($purchase->due_amount > 0)
            <button type="button"
                    onclick="window.openPurchasePayModal({ id: {{ $purchase->id }}, ref: @json($purchase->reference), due: {{ $purchase->due_amount }} })"
                    class="mt-4 w-full py-2 rounded-lg border border-ai-purple text-ai-purple text-sm font-medium hover:bg-violet-50">Make a payment</button>
            @elseif ($refundOwed > 0)
            <button type="button"
                    onclick="document.getElementById('purchase-refund-modal').classList.remove('hidden')"
                    class="mt-4 w-full py-2 rounded-lg border border-ai-cyan text-ai-cyan text-sm font-medium hover:bg-cyan-50">Get Refund</button>
            @else
            <button type="button" disabled class="mt-4 w-full py-2 rounded-lg border border-slate-200 text-slate-400 text-sm font-medium opacity-50 cursor-not-allowed">Make a payment</button>
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

@if ($refundOwed > 0)
<div id="purchase-refund-modal" class="hidden fixed inset-0 z-[300] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40" onclick="document.getElementById('purchase-refund-modal').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
        <h2 class="text-lg font-bold text-slate-900 mb-1">Get Refund</h2>
        <p class="text-sm text-slate-500 mb-4">Refund due from supplier: {{ $fmt($refundOwed) }}</p>
        <form method="POST" action="{{ route('purchases.refund', $purchase) }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-slate-700">Amount</label>
                <input type="number" name="amount" step="0.01" min="0.01" max="{{ $refundOwed }}" value="{{ $refundOwed }}" required class="w-full mt-1 rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Date</label>
                <input type="date" name="refund_date" value="{{ date('Y-m-d') }}" required class="w-full mt-1 rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Method</label>
                <select name="payment_method" class="w-full mt-1 rounded-lg border-slate-300 text-sm">
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                    <option value="cheque">Cheque</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Reference</label>
                <input type="text" name="payment_reference" class="w-full mt-1 rounded-lg border-slate-300 text-sm" placeholder="Optional">
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="document.getElementById('purchase-refund-modal').classList.add('hidden')" class="flex-1 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="flex-1 py-2 rounded-lg bg-ai-navy hover:bg-slate-900 text-white text-sm font-medium">Record refund</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
