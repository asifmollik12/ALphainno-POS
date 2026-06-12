@extends('layouts.dashboard')

@section('title', $product->name)

@section('content')
@php
    $fmt = fn ($n) => $currency . number_format($n, 2);
    $barcodeValue = $product->barcode ?: ($product->sku ?: (string) $product->id);
    $isShortage = $product->isShortage();
@endphp

<div class="flex flex-wrap items-start justify-between gap-4 mb-6">
    <div>
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-bold text-slate-900">{{ $product->name }}</h1>
            @if ($isShortage)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Shortage</span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Active</span>
            @endif
        </div>
        <p class="text-slate-500 text-sm mt-1">SKU: <span class="font-medium text-slate-700">{{ $product->sku ?: '—' }}</span></p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('products.edit', $product) }}" class="px-4 py-2 rounded-lg bg-ai-purple hover:bg-violet-500 text-white text-sm font-medium">Edit Product</a>
        <a href="{{ request('from') === 'shortage' ? route('inventory.shortage') : route('products.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50">Back</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
    {{-- Left: image + barcode --}}
    <div class="lg:col-span-4 space-y-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="aspect-square bg-slate-50 flex items-center justify-center p-6">
                @if ($product->imageUrl())
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain rounded-lg">
                @else
                    <div class="text-center text-slate-300">
                        <svg class="w-24 h-24 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-xs text-slate-400">No product image</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 text-center">
            <svg id="product-barcode" class="mx-auto max-w-full h-16"></svg>
            <p class="mt-2 text-sm font-mono text-slate-600 tracking-wider">{{ $barcodeValue }}</p>
        </div>
    </div>

    {{-- Right: detail cards --}}
    <div class="lg:col-span-8 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">Product Pricing</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Selling Price</dt>
                        <dd class="font-semibold text-slate-900">{{ $fmt($product->price) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Purchase Price</dt>
                        <dd class="font-semibold text-slate-900">{{ $fmt($product->cost_price) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">Stock Details</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Quantity</dt>
                        <dd class="font-bold {{ $isShortage ? 'text-red-600' : 'text-emerald-600' }}">{{ $product->stock }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">ReOrder Quantity</dt>
                        <dd class="font-semibold text-slate-900">{{ $product->min_stock }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">Specifications</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                <div class="flex justify-between gap-4 sm:block">
                    <dt class="text-slate-500">Brand</dt>
                    <dd class="font-medium text-slate-900 sm:mt-0.5">{{ $product->brand ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 sm:block">
                    <dt class="text-slate-500">Category</dt>
                    <dd class="font-medium text-slate-900 sm:mt-0.5">{{ $product->category ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 sm:block">
                    <dt class="text-slate-500">Sub-category</dt>
                    <dd class="font-medium text-slate-900 sm:mt-0.5">{{ $product->category ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 sm:block">
                    <dt class="text-slate-500">UoM</dt>
                    <dd class="font-medium text-slate-900 sm:mt-0.5">{{ $product->unit ?: 'Pcs' }}</dd>
                </div>
            </dl>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">Item Details</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Vat/Tax</dt>
                        <dd class="font-medium text-slate-900">0%</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Discount</dt>
                        <dd class="font-medium text-slate-900">0%</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Date</dt>
                        <dd class="font-medium text-slate-900">{{ $product->created_at->format('d/m/Y') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">Gallery Images</h2>
                <p class="text-sm text-slate-400 py-6 text-center">No gallery images available.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">Product Description</h2>
            <p class="text-sm text-slate-400">No description available.</p>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
JsBarcode('#product-barcode', @json($barcodeValue), {
    format: 'CODE128',
    lineColor: '#0c1222',
    width: 1.6,
    height: 56,
    displayValue: false,
    margin: 0,
});
</script>
@endpush
@endsection
