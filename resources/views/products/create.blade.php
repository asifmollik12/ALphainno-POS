@extends('layouts.dashboard')

@section('title', 'Create Product')

@section('content')
@include('products._form', [
    'product' => null,
    'action' => route('products.store'),
    'categories' => $categories,
    'brands' => $brands,
    'uomOptions' => $uomOptions,
    'discountOptions' => $discountOptions,
    'taxOptions' => $taxOptions,
])
@endsection
