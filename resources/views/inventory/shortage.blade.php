@extends('layouts.dashboard')
@section('title', 'Shortage Products')
@section('content')
@include('partials.page-header', ['title' => 'Shortage Products', 'subtitle' => 'Products at or below minimum stock level'])
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500"><tr><th class="px-4 py-3 text-left">Product</th><th class="px-4 py-3 text-right">Stock</th><th class="px-4 py-3 text-right">Min</th><th class="px-4 py-3 text-right">Need</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($products as $p)
            <tr><td class="px-4 py-3 font-medium">{{ $p->name }}</td><td class="px-4 py-3 text-right text-red-600 font-semibold">{{ $p->stock }}</td><td class="px-4 py-3 text-right">{{ $p->min_stock }}</td><td class="px-4 py-3 text-right">{{ max($p->min_stock - $p->stock, 0) }}</td></tr>
            @empty
            <tr><td colspan="4" class="px-4 py-12 text-center text-slate-400">All products are sufficiently stocked.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
