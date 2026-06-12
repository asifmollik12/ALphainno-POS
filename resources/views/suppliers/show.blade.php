@extends('layouts.dashboard')

@section('title', $supplier->name)

@section('content')
@php
    $fmt = fn ($n) => $currency . number_format($n, 2);
@endphp

<div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
    <div class="xl:col-span-8 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-bold text-slate-900">Supplier Details</h1>
            <div class="flex gap-2">
                <a href="{{ route('suppliers.edit', $supplier) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-ai-navy text-white hover:bg-slate-900" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
                <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Delete this supplier?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-red-200 bg-white text-red-600 hover:bg-red-50" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ tab: 'invoices' }">
            <div class="flex border-b border-slate-200 text-sm overflow-x-auto">
                <button type="button" @click="tab = 'invoices'" :class="tab === 'invoices' ? 'border-b-2 border-ai-purple text-ai-purple font-semibold' : 'text-slate-500'" class="px-5 py-3 whitespace-nowrap">Supplier Invoice {{ $supplier->purchases->count() }}</button>
                <button type="button" @click="tab = 'returns'" :class="tab === 'returns' ? 'border-b-2 border-ai-purple text-ai-purple font-semibold' : 'text-slate-500'" class="px-5 py-3 whitespace-nowrap">Supplier Return Invoice {{ $returns->count() }}</button>
                <button type="button" @click="tab = 'transactions'" :class="tab === 'transactions' ? 'border-b-2 border-ai-purple text-ai-purple font-semibold' : 'text-slate-500'" class="px-5 py-3 whitespace-nowrap">Transactions</button>
            </div>

            <div x-show="tab === 'invoices'" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-ai-mist text-slate-600 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Invoice</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-right">Total Amount</th>
                            <th class="px-4 py-3 text-right">Total Tax</th>
                            <th class="px-4 py-3 text-right">Paid</th>
                            <th class="px-4 py-3 text-right">Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($supplier->purchases as $purchase)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3"><a href="{{ route('purchases.show', $purchase) }}" class="text-ai-cyan font-medium hover:underline">{{ $purchase->reference }}</a></td>
                            <td class="px-4 py-3 text-slate-600">{{ $purchase->purchase_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-right">{{ $fmt($purchase->total) }}</td>
                            <td class="px-4 py-3 text-right">{{ $fmt($purchase->tax_amount ?? 0) }}</td>
                            <td class="px-4 py-3 text-right">{{ $fmt($purchase->paid_amount) }}</td>
                            <td class="px-4 py-3 text-right {{ $purchase->due_amount > 0 ? 'text-red-600 font-semibold' : '' }}">{{ $fmt($purchase->due_amount) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-16 text-center text-slate-400">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                            No Records Found
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div x-show="tab === 'returns'" x-cloak class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-ai-mist text-slate-600 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Reference</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($returns as $return)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $return->reference }}</td>
                            <td class="px-4 py-3">{{ $return->return_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-right">{{ $fmt($return->total) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-16 text-center text-slate-400">No return invoices found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div x-show="tab === 'transactions'" x-cloak class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-ai-mist text-slate-600 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-left">Reference</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $txn)
                        <tr>
                            <td class="px-4 py-3">{{ $txn->transaction_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3">{{ $txn->description }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $txn->reference }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ $fmt($txn->amount) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-16 text-center text-slate-400">No transactions recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="xl:col-span-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="text-center pb-4 border-b border-slate-100">
                <div class="w-16 h-16 mx-auto rounded-full bg-ai-mist flex items-center justify-center text-ai-navy text-2xl font-bold mb-3">{{ strtoupper(substr($supplier->name, 0, 1)) }}</div>
                <h2 class="font-bold text-lg text-slate-900">{{ $supplier->name }}</h2>
            </div>
            <dl class="space-y-3 text-sm">
                <div><dt class="text-slate-500">Email</dt><dd class="font-medium break-all">{{ $supplier->email ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">Phone</dt><dd class="font-medium">{{ $supplier->phone ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">Address</dt><dd>{{ $supplier->address ?: '—' }}</dd></div>
            </dl>
            <dl class="space-y-2 text-sm border-t border-slate-100 pt-4">
                <div class="flex justify-between"><dt class="text-slate-500">Total Amount</dt><dd class="font-medium">{{ number_format($stats['total'], 0) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Paid Amount</dt><dd class="text-emerald-600">- {{ number_format($stats['paid'], 0) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Return Amount</dt><dd>- {{ number_format($stats['return'], 0) }}</dd></div>
                <div class="flex justify-between font-bold pt-2 border-t"><dt>Due Amount</dt><dd class="{{ $stats['due'] > 0 ? 'text-red-600' : '' }}">{{ number_format($stats['due'], 0) }}</dd></div>
            </dl>
        </div>
    </div>
</div>
@endsection
