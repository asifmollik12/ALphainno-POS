@extends('layouts.dashboard')

@section('title', 'Products')

@section('content')
@include('partials.page-header', [
    'title' => 'Products',
    'subtitle' => 'Manage inventory products',
    'actionUrl' => route('products.create'),
    'actionLabel' => '+ Add Product',
])

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-4 py-3">Photo</th>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">SKU</th>
                <th class="px-4 py-3 text-right">Cost</th>
                <th class="px-4 py-3 text-right">Price</th>
                <th class="px-4 py-3 text-right">Stock</th>
                <th class="px-4 py-3 text-right">Min</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($products as $product)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3">
                    @if ($product->imageUrl())
                        <img src="{{ $product->imageUrl() }}" alt="" class="w-10 h-10 rounded object-cover border">
                    @else
                        <div class="w-10 h-10 rounded bg-slate-100 flex items-center justify-center text-slate-400 text-xs font-bold">{{ strtoupper(substr($product->name,0,1)) }}</div>
                    @endif
                </td>
                <td class="px-4 py-3 font-medium">{{ $product->name }}</td>
                <td class="px-4 py-3 text-slate-500">{{ $product->sku ?: '—' }}</td>
                <td class="px-4 py-3 text-right">{{ number_format($product->cost_price, 2) }}</td>
                <td class="px-4 py-3 text-right">{{ number_format($product->price, 2) }}</td>
                <td class="px-4 py-3 text-right {{ $product->isShortage() ? 'text-amber-600 font-semibold' : '' }}">{{ $product->stock }}</td>
                <td class="px-4 py-3 text-right">{{ $product->min_stock }}</td>
                <td class="px-4 py-3 text-right space-x-2">
                    <a href="{{ route('products.edit', $product) }}" class="text-blue-600 hover:underline">Edit</a>
                    <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-12 text-center text-slate-400">No products yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
