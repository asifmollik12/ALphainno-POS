@extends('layouts.dashboard')
@section('title', 'Purchase Return')
@section('content')
@include('partials.page-header', ['title' => 'Create Purchase Return'])
<form method="POST" action="{{ route('purchase-returns.store') }}" class="space-y-4" id="return-form">@csrf
<div class="bg-white rounded-xl border border-slate-200 p-5 grid sm:grid-cols-2 gap-4">
    <div>
        <label class="text-sm font-medium text-slate-700">Purchase invoice (optional)</label>
        <select name="purchase_id" id="purchase_id" class="w-full mt-1 rounded-lg border-slate-300 text-sm">
            <option value="">— None —</option>
            @foreach($purchases as $p)
                <option value="{{ $p->id }}" @selected(old('purchase_id', $selectedPurchase?->id) == $p->id)>{{ $p->reference }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">Supplier</label>
        <select name="supplier_id" class="w-full mt-1 rounded-lg border-slate-300 text-sm">
            <option value="">—</option>
            @foreach($suppliers as $s)
                <option value="{{ $s->id }}" @selected(old('supplier_id', $selectedPurchase?->supplier_id) == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">Return date</label>
        <input type="date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" required class="w-full mt-1 rounded-lg border-slate-300 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">Notes</label>
        <input type="text" name="notes" value="{{ old('notes') }}" class="w-full mt-1 rounded-lg border-slate-300 text-sm" placeholder="Optional notes">
    </div>
</div>
<div class="bg-white rounded-xl border border-slate-200 p-5">
    <table class="w-full text-sm" id="items-table">
        <thead><tr><th class="text-left py-2">Product</th><th class="text-right py-2">Qty</th><th class="text-right py-2">Unit cost</th></tr></thead>
        <tbody>
            @if ($selectedPurchase && $selectedPurchase->items->isNotEmpty())
                @foreach ($selectedPurchase->items as $idx => $line)
                <tr>
                    <td>
                        <select name="items[{{ $idx }}][product_id]" class="w-full rounded-lg border-slate-300 text-sm">
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" @selected($p->id == $line->product_id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="items[{{ $idx }}][quantity]" value="{{ $line->quantity }}" min="1" class="w-24 rounded-lg border-slate-300 text-right text-sm"></td>
                    <td><input type="number" step="0.01" name="items[{{ $idx }}][unit_cost]" value="{{ $line->unit_cost }}" class="w-32 rounded-lg border-slate-300 text-right text-sm"></td>
                </tr>
                @endforeach
            @else
            <tr>
                <td><select name="items[0][product_id]" class="w-full rounded-lg border-slate-300 text-sm">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></td>
                <td><input type="number" name="items[0][quantity]" value="1" min="1" class="w-24 rounded-lg border-slate-300 text-right text-sm"></td>
                <td><input type="number" step="0.01" name="items[0][unit_cost]" value="0" class="w-32 rounded-lg border-slate-300 text-right text-sm"></td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
<div class="flex gap-3">
    @if ($selectedPurchase)
        <a href="{{ route('purchases.show', $selectedPurchase) }}" class="px-5 py-2 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
    @endif
    <button class="px-5 py-2 bg-ai-navy hover:bg-slate-900 text-white rounded-lg text-sm font-medium">Save return</button>
</div>
</form>
@endsection
