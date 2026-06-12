@extends('layouts.dashboard')
@section('title', 'Edit Product')
@section('content')
@include('partials.page-header', ['title' => 'Edit Product'])
@include('products._form', ['product' => $product, 'action' => route('products.update', $product)])
@endsection
