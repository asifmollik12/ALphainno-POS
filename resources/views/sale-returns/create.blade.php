@extends('layouts.dashboard')
@section('title', 'Sale Return')
@section('content')
@include('partials.page-header', ['title' => 'Create Sale Return'])
<form method="POST" action="{{ route('sale-returns.store') }}">@csrf
<div class="bg-white rounded-xl border p-5 grid sm:grid-cols-2 gap-4 mb-4"><div><label class="text-sm">Linked sale</label><select name="sale_id" class="w-full mt-1 rounded-lg border-slate-300"><option value="">—</option>@foreach($sales as $s)<option value="{{ $s->id }}">{{ $s->reference }}</option>@endforeach</select></div><div><label class="text-sm">Return date</label><input type="date" name="return_date" value="{{ date('Y-m-d') }}" required class="w-full mt-1 rounded-lg border-slate-300"></div></div>
<div class="bg-white rounded-xl border p-5 mb-4"><table class="w-full text-sm"><tr><td><select name="items[0][product_id]" class="w-full rounded-lg border-slate-300">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></td><td><input type="number" name="items[0][quantity]" value="1" min="1" class="w-24 rounded-lg border-slate-300"></td><td><input type="number" step="0.01" name="items[0][unit_price]" value="0" class="w-32 rounded-lg border-slate-300"></td></tr></table></div>
<button class="px-5 py-2 bg-violet-600 text-white rounded-lg">Save return</button></form>
@endsection
