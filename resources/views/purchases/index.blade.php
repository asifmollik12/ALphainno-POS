@extends('layouts.dashboard')

@section('title', 'Purchase Invoices')

@section('content')
@php
    $fmt = fn ($n) => $currency . number_format($n, 2);
    $fmtCompact = function ($n) use ($currency) {
        if ($n >= 1_000_000) return $currency . number_format($n / 1_000_000, 1) . 'M';
        if ($n >= 1_000) return $currency . number_format($n / 1_000, 1) . 'K';
        return $currency . number_format($n, 0);
    };
@endphp

<div class="flex flex-wrap items-center justify-between gap-4 mb-5">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Purchase Invoice</h1>
        <p class="text-slate-500 text-sm mt-1">{{ number_format($stats['count']) }} invoices in selected period</p>
    </div>
    <a href="{{ route('purchases.create') }}" class="px-4 py-2 rounded-lg bg-ai-navy hover:bg-slate-900 text-white text-sm font-medium">+ Create Purchase</a>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
        <div class="w-11 h-11 rounded-lg bg-ai-sky flex items-center justify-center text-ai-cyan">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div>
            <div class="text-xs text-slate-500 font-medium">Total Purchases #{{ $stats['count'] }}</div>
            <div class="text-2xl font-bold text-slate-900">{{ $fmtCompact($stats['total']) }}</div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
        <div class="w-11 h-11 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <div>
            <div class="text-xs text-slate-500 font-medium">Total Purchase Paid</div>
            <div class="text-2xl font-bold text-slate-900">{{ $fmtCompact($stats['paid']) }}</div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
        <div class="w-11 h-11 rounded-lg bg-red-50 flex items-center justify-center text-red-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-xs text-slate-500 font-medium">Total Purchase Due</div>
            <div class="text-2xl font-bold text-red-600">{{ $fmtCompact($stats['due']) }}</div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
        <div class="w-11 h-11 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
        </div>
        <div>
            <div class="text-xs text-slate-500 font-medium">Total Purchase Return</div>
            <div class="text-2xl font-bold text-slate-900">{{ $fmtCompact($stats['returns']) }}</div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4 p-4">
    <form method="GET" class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border border-slate-200 text-sm px-3 py-2">
            <span class="text-slate-400">to</span>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border border-slate-200 text-sm px-3 py-2">
            <button type="submit" class="px-3 py-2 rounded-lg bg-ai-mist text-sm font-medium text-slate-700 hover:bg-slate-200">Apply</button>
        </div>
        <div class="flex flex-wrap items-center gap-2 flex-1 justify-end min-w-[240px]">
            <div class="relative flex-1 min-w-[160px] max-w-xs">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search invoice..." class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-200 text-sm">
            </div>
            <a href="{{ route('purchases.print', request()->only(['from','to','q'])) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">Print PDF</a>
            <a href="{{ route('purchases.export', request()->only(['from','to'])) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">Download CSV</a>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[900px]">
            <thead class="bg-ai-navy text-white text-left text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-3">Invoice</th>
                    <th class="px-4 py-3">Supplier</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">Paid</th>
                    <th class="px-4 py-3 text-right">Due</th>
                    <th class="px-4 py-3 text-right">Tax</th>
                    <th class="px-4 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($purchases as $purchase)
                @php $gmailUrl = $purchase->gmailComposeUrl(); @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('purchases.show', $purchase) }}" class="font-medium text-ai-cyan hover:underline">{{ $purchase->reference }}</a>
                    </td>
                    <td class="px-4 py-3">{{ $purchase->supplier->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $purchase->purchase_date->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-right font-medium">{{ $fmt($purchase->total) }}</td>
                    <td class="px-4 py-3 text-right">{{ $fmt($purchase->paid_amount) }}</td>
                    <td class="px-4 py-3 text-right {{ $purchase->due_amount > 0 ? 'text-red-600 font-semibold' : '' }}">{{ $fmt($purchase->due_amount) }}</td>
                    <td class="px-4 py-3 text-right text-slate-500">{{ $fmt($purchase->tax_amount ?? 0) }}</td>
                    <td class="px-4 py-3 text-center">
                        <x-row-actions-dropdown>
                            <a href="{{ route('purchases.show', $purchase) }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-slate-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View
                            </a>
                            <button type="button" @click="openPayModal({ id: {{ $purchase->id }}, ref: @json($purchase->reference), due: {{ $purchase->due_amount }} })" class="w-full flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-slate-700 text-left">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Make a payment
                            </button>
                            <a href="{{ $gmailUrl ?? '#' }}" target="_blank" rel="noopener" @class(['flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-slate-700', 'pointer-events-none opacity-50' => ! $gmailUrl]) @click="close()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                Email
                            </a>
                        </x-row-actions-dropdown>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-16 text-center text-slate-400">No purchase invoices in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $purchases->links() }}</div>
@endsection
