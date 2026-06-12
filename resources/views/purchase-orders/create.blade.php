@extends('layouts.dashboard')
@section('title', 'Purchase Order')
@section('content')
@include('partials.page-header', ['title' => 'Create Purchase Order'])
<form method="POST" action="{{ route('purchase-orders.store') }}">@csrf
<div class="bg-white rounded-xl border p-5 grid sm:grid-cols-3 gap-4 mb-4"><div><label class="text-sm">Supplier</label><select name="supplier_id" class="w-full mt-1 rounded-lg border-slate-300"><option value="">—</option>@foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div><div><label class="text-sm">Order date</label><input type="date" name="order_date" value="{{ date('Y-m-d') }}" required class="w-full mt-1 rounded-lg border-slate-300"></div><div><label class="text-sm">Expected date</label><input type="date" name="expected_date" class="w-full mt-1 rounded-lg border-slate-300"></div></div>
<div class="bg-white rounded-xl border p-5 mb-4"><table class="w-full text-sm"><tr><td><select name="items[0][product_id]" class="w-full rounded-lg border-slate-300">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></td><td><input type="number" name="items[0][quantity]" value="1" min="1" class="w-24 rounded-lg border-slate-300"></td><td><input type="number" step="0.01" name="items[0][unit_cost]" value="0" class="w-32 rounded-lg border-slate-300"></td></tr></table></div>
<button class="px-5 py-2 bg-violet-600 text-white rounded-lg">Save order</button></form>
@endsection
