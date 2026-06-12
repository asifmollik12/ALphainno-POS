@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<div class="max-w-lg">
    <h1 class="text-xl font-semibold text-white mb-6">Add product</h1>

    @include('products._form', ['product' => null, 'action' => route('products.store')])
</div>
@endsection
