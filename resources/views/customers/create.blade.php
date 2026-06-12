@extends('layouts.dashboard')
@section('title', 'Add Customer')
@section('content')
@include('partials.page-header', ['title' => 'Add Customer'])
@include('partials.contact-form', ['action' => route('customers.store'), 'model' => null])
@endsection
