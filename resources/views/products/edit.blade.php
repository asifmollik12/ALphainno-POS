@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="max-w-lg">
    <h1 class="text-xl font-semibold text-white mb-6">Edit product</h1>

    @include('products._form', ['product' => $product, 'action' => route('products.update', $product)])
</div>
@endsection
