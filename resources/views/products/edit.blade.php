@extends('layouts.dashboard')

@section('title', 'Update Product')

@section('content')
@include('products._form', [
    'product' => $product,
    'action' => route('products.update', $product),
    'categories' => $categories,
    'brands' => $brands,
    'uomOptions' => $uomOptions,
    'discountOptions' => $discountOptions,
    'taxOptions' => $taxOptions,
])

<div class="mt-3 flex justify-end">
    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete this product?')">
        @csrf @method('DELETE')
        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">Delete product</button>
    </form>
</div>
@endsection
