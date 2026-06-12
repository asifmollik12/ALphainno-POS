@extends('layouts.dashboard')
@section('title', 'Edit Customer')
@section('content')
@include('partials.page-header', ['title' => 'Edit Customer'])
@include('partials.contact-form', ['action' => route('customers.update', $customer), 'model' => $customer])
@endsection
