@extends('layouts.dashboard')

@section('title', 'Shortage Products')

@section('content')
@php $fmt = fn ($n) => $currency . number_format($n, 2); @endphp

<div class="flex flex-wrap items-center justify-between gap-4 mb-5">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Shortage Products</h1>
        <p class="text-slate-500 text-sm mt-1">{{ number_format($totalShort) }} products at or below reorder level</p>
    </div>
    <a href="{{ route('products.create') }}" class="px-4 py-2 rounded-lg bg-ai-navy hover:bg-slate-900 text-white text-sm font-medium">+ Create Product</a>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4 p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-center gap-2 flex-1 min-w-[200px]">
            <div class="relative flex-1 min-w-[180px] max-w-md">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search shortage products..." class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-200 text-sm focus:border-ai-purple focus:ring-ai-purple/20">
            </div>
            <button type="submit" class="px-3 py-2 rounded-lg bg-ai-mist text-sm font-medium text-slate-700 hover:bg-slate-200">Search</button>
            @if (request('q'))
                <a href="{{ route('inventory.shortage') }}" class="px-3 py-2 text-sm text-slate-500">Clear</a>
            @endif
        </form>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('inventory.shortage.print') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print PDF
            </a>
            <a href="{{ route('inventory.shortage.export') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download CSV
            </a>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[800px]">
            <thead class="bg-ai-mist text-slate-600 text-left text-xs uppercase tracking-wide border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 w-10"><input type="checkbox" id="select-all" class="rounded border-slate-300"></th>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3 text-right">QTY</th>
                    <th class="px-4 py-3 text-right">Purchase Price</th>
                    <th class="px-4 py-3 text-right">Sale Price</th>
                    <th class="px-4 py-3 text-right">Reorder QTY</th>
                    <th class="px-4 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($products as $product)
                <tr class="hover:bg-red-50/30">
                    <td class="px-4 py-3"><input type="checkbox" class="row-check rounded border-slate-300" value="{{ $product->id }}"></td>
                    <td class="px-4 py-3 text-slate-500">{{ $product->id }}</td>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $product->name }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $product->sku ?: '—' }}</td>
                    <td class="px-4 py-3 text-right text-red-600 font-bold">{{ $product->stock }}</td>
                    <td class="px-4 py-3 text-right">{{ $fmt($product->cost_price) }}</td>
                    <td class="px-4 py-3 text-right">{{ $fmt($product->price) }}</td>
                    <td class="px-4 py-3 text-right">{{ $product->min_stock }}</td>
                    <td class="px-4 py-3 text-center relative">
                        @include('products._actions-menu', ['product' => $product, 'from' => 'shortage'])
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-16 text-center text-slate-400">All products are sufficiently stocked.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $products->links() }}</div>

@push('scripts')
<script>
document.getElementById('select-all')?.addEventListener('change', e => {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = e.target.checked);
});
</script>
@endpush
@endsection
