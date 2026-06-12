@extends('layouts.dashboard')

@section('title', 'Products')

@section('content')
@php
    $fmtMoney = fn ($n) => $currency . number_format($n, 0);
    $fmtCompact = function ($n) use ($currency) {
        if ($n >= 1_000_000) return $currency . number_format($n / 1_000_000, 1) . 'M';
        if ($n >= 1_000) return $currency . number_format($n / 1_000, 1) . 'K';
        return $currency . number_format($n, 0);
    };
@endphp

<div class="flex flex-wrap items-center justify-between gap-4 mb-5">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Products</h1>
        <p class="text-slate-500 text-sm mt-1">Manage inventory products</p>
    </div>
    <a href="{{ route('products.create') }}" class="px-4 py-2 rounded-lg bg-ai-navy hover:bg-slate-900 text-white text-sm font-medium">+ Create Product</a>
</div>

{{-- KPI cards --}}
<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
        <div class="w-11 h-11 rounded-lg bg-ai-sky flex items-center justify-center text-ai-cyan">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <div>
            <div class="text-xs text-slate-500 font-medium">Unique Product</div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['unique']) }}</div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
        <div class="w-11 h-11 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
        </div>
        <div>
            <div class="text-xs text-slate-500 font-medium">Inventory Sale Value</div>
            <div class="text-2xl font-bold text-slate-900">{{ $fmtCompact($stats['sale_value']) }}</div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
        <div class="w-11 h-11 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-xs text-slate-500 font-medium">Inventory Purchase Value</div>
            <div class="text-2xl font-bold text-slate-900">{{ $fmtCompact($stats['purchase_value']) }}</div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
        <div class="w-11 h-11 rounded-lg bg-red-50 flex items-center justify-center text-red-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
            <div class="text-xs text-slate-500 font-medium">Short Product</div>
            <div class="text-2xl font-bold text-red-600">{{ number_format($stats['short_count']) }}</div>
        </div>
    </div>
</div>

{{-- Toolbar --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4 p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-center gap-2 flex-1 min-w-[200px]">
            <div class="relative flex-1 min-w-[180px] max-w-md">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products..." class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-200 text-sm focus:border-ai-purple focus:ring-ai-purple/20">
            </div>
            <button type="submit" class="px-3 py-2 rounded-lg bg-ai-mist text-sm font-medium text-slate-700 hover:bg-slate-200">Search</button>
            @if (request('q'))
                <a href="{{ route('products.index') }}" class="px-3 py-2 text-sm text-slate-500 hover:text-slate-800">Clear</a>
            @endif
        </form>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('products.print') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print PDF
            </a>
            <a href="{{ route('products.export') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download CSV
            </a>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[960px]">
            <thead class="bg-ai-navy text-white text-left text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Image</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Brand</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3 text-right">Purchase Price</th>
                    <th class="px-4 py-3 text-right">Sale Price</th>
                    <th class="px-4 py-3 text-right">VAT</th>
                    <th class="px-4 py-3 text-right">Quantity</th>
                    <th class="px-4 py-3">UOM</th>
                    <th class="px-4 py-3 text-right">Reorder Qty</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($products as $product)
                <tr class="hover:bg-ai-mist/50">
                    <td class="px-4 py-3 text-slate-500">{{ $product->id }}</td>
                    <td class="px-4 py-3">
                        @if ($product->imageUrl())
                            <img src="{{ $product->imageUrl() }}" alt="" class="w-9 h-9 rounded object-cover border">
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $product->name }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $product->brand ?: '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $product->category ?: '—' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $product->sku ?: '—' }}</td>
                    <td class="px-4 py-3 text-right">{{ $fmtMoney($product->cost_price) }}</td>
                    <td class="px-4 py-3 text-right font-medium">{{ $fmtMoney($product->price) }}</td>
                    <td class="px-4 py-3 text-right text-slate-600">{{ number_format($product->tax_rate ?? 0, 1) }}%</td>
                    <td class="px-4 py-3 text-right {{ $product->isShortage() ? 'text-red-600 font-semibold' : '' }}">{{ $product->stock }}</td>
                    <td class="px-4 py-3">{{ $product->unit ?? 'Pcs' }}</td>
                    <td class="px-4 py-3 text-right">{{ $product->min_stock }}</td>
                    <td class="px-4 py-3 text-center relative">
                        @include('products._actions-menu', ['product' => $product])
                    </td>
                </tr>
                @empty
                <tr><td colspan="13" class="px-4 py-16 text-center text-slate-400">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
