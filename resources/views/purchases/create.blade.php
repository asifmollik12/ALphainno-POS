@extends('layouts.dashboard')

@section('title', 'Create Purchase')

@section('content')
@php $fmt = fn ($n) => $currency . number_format($n, 2); @endphp

<div class="mb-5">
    <h1 class="text-xl font-bold text-slate-900">Create Purchase</h1>
    <p class="text-slate-500 text-sm mt-1">Record a new purchase invoice and update stock</p>
</div>

<form method="POST" action="{{ route('purchases.store') }}" id="purchase-form">
    @csrf
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
        <div class="xl:col-span-8 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[720px]" id="items-table">
                    <thead class="bg-ai-mist text-slate-600 text-xs uppercase">
                        <tr>
                            <th class="px-3 py-3 w-10">#</th>
                            <th class="px-3 py-3 text-left">Product</th>
                            <th class="px-3 py-3 text-right w-24">Quantity</th>
                            <th class="px-3 py-3 text-right w-32">Purchase Price</th>
                            <th class="px-3 py-3 text-right w-32">Selling Price</th>
                            <th class="px-3 py-3 text-right w-28">Amount</th>
                            <th class="px-3 py-3 text-right w-20">Tax%</th>
                            <th class="px-3 py-3 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <tr class="item-row border-t border-slate-100">
                            <td class="px-3 py-2 text-slate-400 row-num">1</td>
                            <td class="px-3 py-2">
                                <select name="items[0][product_id]" required class="product-select w-full rounded-lg border border-slate-200 text-sm py-2">
                                    <option value="">Select Product</option>
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2"><input type="number" name="items[0][quantity]" value="1" min="1" required class="qty-input w-full rounded-lg border border-slate-200 text-sm py-2 text-right"></td>
                            <td class="px-3 py-2"><input type="number" step="0.01" name="items[0][unit_cost]" value="0" required class="cost-input w-full rounded-lg border border-slate-200 text-sm py-2 text-right"></td>
                            <td class="px-3 py-2"><input type="number" step="0.01" readonly class="sell-input w-full rounded-lg border border-slate-100 bg-slate-50 text-sm py-2 text-right" value="0"></td>
                            <td class="px-3 py-2 text-right font-medium amount-cell">0.00</td>
                            <td class="px-3 py-2"><input type="number" step="0.01" name="items[0][tax_rate]" value="0" min="0" class="tax-input w-full rounded-lg border border-slate-200 text-sm py-2 text-right"></td>
                            <td class="px-3 py-2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                <button type="button" id="add-row" class="inline-flex items-center gap-1 text-sm font-medium text-ai-purple hover:text-violet-600">+ Add Product</button>
            </div>
        </div>

        <div class="xl:col-span-4 space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
                <div>
                    <label class="text-sm font-medium text-slate-700">Supplier <span class="text-red-500">*</span></label>
                    <select name="supplier_id" required class="w-full mt-1 rounded-lg border border-slate-200 text-sm py-2">
                        <option value="">Select a supplier</option>
                        @foreach ($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required class="w-full mt-1 rounded-lg border border-slate-200 text-sm py-2">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Note</label>
                    <textarea name="notes" rows="3" placeholder="Note" class="w-full mt-1 rounded-lg border border-slate-200 text-sm py-2 px-3"></textarea>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Total amount</span><span id="sum-subtotal">0.00</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Total tax amount</span><span id="sum-tax">0.00</span></div>
                <div class="flex justify-between font-bold text-base pt-2 border-t"><span>Total Payable</span><span id="sum-total">0.00</span></div>
                <div class="flex justify-between text-red-600"><span>Due Amount</span><span id="sum-due">0.00</span></div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Paid Amount</label>
                    <input type="number" step="0.01" name="paid_amount" id="paid-amount" value="0" min="0" class="w-full mt-1 rounded-lg border border-slate-200 text-sm py-2">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Payment Method</label>
                    <select name="payment_method" class="w-full mt-1 rounded-lg border border-slate-200 text-sm py-2">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="mobile">Mobile Banking</option>
                        <option value="bank">Bank Transfer</option>
                    </select>
                </div>
                <button type="submit" class="w-full py-2.5 rounded-lg bg-ai-navy hover:bg-slate-900 text-white font-medium">Create Purchase</button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
const catalog = @json($productCatalog);
const currency = @json($currency);
let rowIdx = 1;

function findProduct(id) {
    return catalog.find(p => String(p.id) === String(id));
}

function lineAmount(row) {
    const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
    const cost = parseFloat(row.querySelector('.cost-input')?.value) || 0;
    const tax = parseFloat(row.querySelector('.tax-input')?.value) || 0;
    const sub = qty * cost;
    return { sub, tax: sub * tax / 100, total: sub + sub * tax / 100 };
}

function recalc() {
    let subtotal = 0, taxTotal = 0;
    document.querySelectorAll('.item-row').forEach((row, i) => {
        row.querySelector('.row-num').textContent = i + 1;
        const { sub, tax, total } = lineAmount(row);
        subtotal += sub;
        taxTotal += tax;
        row.querySelector('.amount-cell').textContent = total.toFixed(2);
    });
    const grand = subtotal + taxTotal;
    const paid = parseFloat(document.getElementById('paid-amount').value) || 0;
    document.getElementById('sum-subtotal').textContent = currency + subtotal.toFixed(2);
    document.getElementById('sum-tax').textContent = currency + taxTotal.toFixed(2);
    document.getElementById('sum-total').textContent = currency + grand.toFixed(2);
    document.getElementById('sum-due').textContent = currency + Math.max(grand - paid, 0).toFixed(2);
}

function bindRow(row) {
    row.querySelector('.product-select')?.addEventListener('change', e => {
        const p = findProduct(e.target.value);
        if (p) {
            row.querySelector('.cost-input').value = p.cost_price;
            row.querySelector('.sell-input').value = p.price;
            row.querySelector('.tax-input').value = p.tax_rate || 0;
        }
        recalc();
    });
    row.querySelectorAll('.qty-input, .cost-input, .tax-input').forEach(el => el.addEventListener('input', recalc));
    row.querySelector('.remove-row')?.addEventListener('click', () => {
        if (document.querySelectorAll('.item-row').length > 1) {
            row.remove();
            recalc();
        }
    });
}

document.querySelectorAll('.item-row').forEach(bindRow);
document.getElementById('paid-amount').addEventListener('input', recalc);

document.getElementById('add-row').addEventListener('click', () => {
    const tbody = document.getElementById('items-body');
    const template = document.querySelector('.item-row');
    const row = template.cloneNode(true);
    row.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${rowIdx}]`);
        if (el.classList.contains('product-select')) el.value = '';
        if (el.classList.contains('qty-input')) el.value = 1;
        if (el.classList.contains('cost-input')) el.value = 0;
        if (el.classList.contains('sell-input')) el.value = 0;
        if (el.classList.contains('tax-input')) el.value = 0;
    });
    row.querySelector('.amount-cell').textContent = '0.00';
    const lastCell = row.querySelector('td:last-child');
    lastCell.innerHTML = '<button type="button" class="remove-row text-red-500 hover:text-red-700">&times;</button>';
    tbody.appendChild(row);
    bindRow(row);
    rowIdx++;
    recalc();
});

recalc();
</script>
@endpush
@endsection
