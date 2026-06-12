@extends('layouts.dashboard')
@section('title', 'Edit Supplier')
@section('content')
@include('partials.page-header', ['title' => 'Edit Supplier'])
@include('partials.contact-form', ['action' => route('suppliers.update', $supplier), 'model' => $supplier])
@endsection
