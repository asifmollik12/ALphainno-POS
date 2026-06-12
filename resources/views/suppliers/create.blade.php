@extends('layouts.dashboard')
@section('title', 'Add Supplier')
@section('content')
@include('partials.page-header', ['title' => 'Add Supplier'])
@include('partials.contact-form', ['action' => route('suppliers.store'), 'model' => null])
@endsection
