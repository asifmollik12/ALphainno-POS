@extends('layouts.dashboard')
@section('title', 'Create Purchase')
@section('content')
@include('partials.page-header', ['title' => 'Create Purchase Invoice'])
<form method="POST" action="{{ route('purchases.store') }}" class="space-y-4">
@csrf
<div class="bg-white rounded-xl border border-slate-200 p-5 grid sm:grid-cols-3 gap-4 shadow-sm">
    <div><label class="text-sm font-medium">Supplier</label><select name="supplier_id" class="w-full mt-1 rounded-lg border-slate-300"><option value="">— Select —</option>@foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
    <div><label class="text-sm font-medium">Purchase date</label><input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required class="w-full mt-1 rounded-lg border-slate-300"></div>
    <div><label class="text-sm font-medium">Paid amount</label><input type="number" step="0.01" name="paid_amount" value="0" class="w-full mt-1 rounded-lg border-slate-300"></div>
</div>
<div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
    <div class="flex justify-between items-center mb-3"><h3 class="font-semibold">Line items</h3><button type="button" id="add-row" class="text-sm text-violet-600">+ Add row</button></div>
    <table class="w-full text-sm" id="items-table"><thead class="text-slate-500"><tr><th class="text-left py-2">Product</th><th class="text-right py-2">Qty</th><th class="text-right py-2">Unit cost</th><th></th></tr></thead>
    <tbody>
        <tr class="item-row"><td class="pr-2"><select name="items[0][product_id]" required class="w-full rounded-lg border-slate-300">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></td><td class="px-2"><input type="number" name="items[0][quantity]" value="1" min="1" required class="w-24 rounded-lg border-slate-300 text-right"></td><td class="px-2"><input type="number" step="0.01" name="items[0][unit_cost]" value="0" required class="w-32 rounded-lg border-slate-300 text-right"></td><td></td></tr>
    </tbody></table>
</div>
<button class="px-5 py-2.5 bg-violet-600 text-white rounded-lg hover:bg-violet-500">Save purchase</button>
</form>
@push('scripts')
<script>
let idx=1; document.getElementById('add-row').onclick=()=>{const tb=document.querySelector('#items-table tbody'); const tr=document.querySelector('.item-row').cloneNode(true); tr.querySelectorAll('[name]').forEach(el=>el.name=el.name.replace(/\[\d+\]/,'['+idx+']')); tr.querySelector('input[type=number]').value=1; tb.appendChild(tr); idx++;};
</script>
@endpush
@endsection
