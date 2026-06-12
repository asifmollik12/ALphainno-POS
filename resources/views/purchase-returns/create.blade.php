@extends('layouts.dashboard')
@section('title', 'Purchase Return')
@section('content')
@include('partials.page-header', ['title' => 'Create Purchase Return'])
<form method="POST" action="{{ route('purchase-returns.store') }}" class="space-y-4">@csrf
<div class="bg-white rounded-xl border p-5 grid sm:grid-cols-2 gap-4"><div><label class="text-sm font-medium">Supplier</label><select name="supplier_id" class="w-full mt-1 rounded-lg border-slate-300"><option value="">—</option>@foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div><div><label class="text-sm font-medium">Return date</label><input type="date" name="return_date" value="{{ date('Y-m-d') }}" required class="w-full mt-1 rounded-lg border-slate-300"></div></div>
<div class="bg-white rounded-xl border p-5"><table class="w-full text-sm"><thead><tr><th class="text-left py-2">Product</th><th class="text-right py-2">Qty</th><th class="text-right py-2">Unit cost</th></tr></thead><tbody><tr><td><select name="items[0][product_id]" class="w-full rounded-lg border-slate-300">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></td><td><input type="number" name="items[0][quantity]" value="1" min="1" class="w-24 rounded-lg border-slate-300 text-right"></td><td><input type="number" step="0.01" name="items[0][unit_cost]" value="0" class="w-32 rounded-lg border-slate-300 text-right"></td></tr></tbody></table></div>
<button class="px-5 py-2 bg-violet-600 text-white rounded-lg">Save return</button></form>
@endsection
