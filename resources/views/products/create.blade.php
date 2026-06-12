@extends('layouts.dashboard')
@section('title', 'Add Product')
@section('content')
@include('partials.page-header', ['title' => 'Add Product'])
@include('products._form', ['product' => null, 'action' => route('products.store')])
@endsection
