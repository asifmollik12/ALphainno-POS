@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold text-white">Products</h1>
        <p class="text-slate-400 text-sm mt-0.5">Add items you want to sell at the counter</p>
    </div>
    <a href="{{ route('products.create') }}"
       class="bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium px-4 py-2 rounded-lg">
        + Add product
    </a>
</div>

@if ($products->isEmpty())
    <div class="text-center py-16 border border-dashed border-slate-700 rounded-xl">
        <p class="text-slate-400 mb-4">No products yet.</p>
        <a href="{{ route('products.create') }}" class="text-emerald-400 hover:text-emerald-300 text-sm">Add your first product →</a>
    </div>
@else
    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full text-sm">
            <thead class="bg-slate-900 text-slate-400 text-left">
                <tr>
                    <th class="px-4 py-3 font-medium">Name</th>
                    <th class="px-4 py-3 font-medium">SKU</th>
                    <th class="px-4 py-3 font-medium text-right">Price</th>
                    <th class="px-4 py-3 font-medium text-right">Stock</th>
                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @foreach ($products as $product)
                <tr class="bg-slate-900/40 hover:bg-slate-900">
                    <td class="px-4 py-3 text-white">{{ $product->name }}</td>
                    <td class="px-4 py-3 text-slate-400">{{ $product->sku ?: '—' }}</td>
                    <td class="px-4 py-3 text-right">৳{{ number_format($product->price, 2) }}</td>
                    <td class="px-4 py-3 text-right">{{ $product->stock }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('products.edit', $product) }}" class="text-emerald-400 hover:text-emerald-300">Edit</a>
                        <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline"
                              onsubmit="return confirm('Delete this product?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
@endif
@endsection
