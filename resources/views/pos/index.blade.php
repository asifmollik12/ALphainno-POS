@extends('layouts.dashboard')

@section('pos_fullscreen', true)

@section('title', 'POS')

@php
    $currency = $setting->currency ?? '৳';
    $warehouse = $setting->warehouse_name ?? 'Main Warehouse';
    $taxRate = (float) ($setting->default_tax_rate ?? 0);
@endphp

@section('content')
<div class="flex flex-col lg:flex-row h-[calc(100vh-0px)] bg-slate-100">
    {{-- Left: Product picker --}}
    <div class="lg:w-[42%] xl:w-[38%] bg-[#e8eaed] border-r border-slate-300 p-3 flex flex-col min-h-[50vh] lg:min-h-0">
        <div class="bg-white rounded-md border border-slate-200 p-3 shadow-sm mb-2">
            <div class="flex mb-2 overflow-hidden rounded-md">
                <button type="button" id="tab-category" class="filter-tab flex-1 py-2.5 text-sm font-bold bg-[#2563eb] text-white">Category</button>
                <button type="button" id="tab-brand" class="filter-tab flex-1 py-2.5 text-sm font-bold bg-[#1e4d3a] text-white/70">Brand</button>
            </div>
            <input type="text" id="search-name" placeholder="Search By Name" class="w-full mb-2 rounded border-slate-300 text-sm py-2 px-3">
            <input type="text" id="scan-barcode" placeholder="Scan Barcode" autofocus class="w-full rounded border-2 border-blue-500 text-sm py-2 px-3 focus:ring-2 focus:ring-blue-300 outline-none">
        </div>

        <div id="filter-chips" class="flex flex-wrap gap-1.5 mb-2 min-h-[24px]"></div>

        <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-2 overflow-y-auto flex-1 pr-1 pb-2">
            @foreach ($products as $product)
            <button type="button"
                    class="product-card group bg-white border border-slate-200 rounded-md overflow-hidden text-left hover:shadow-lg hover:border-blue-400 transition-all active:scale-[0.98]"
                    data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                    data-price="{{ $product->price }}"
                    data-stock="{{ $product->stock }}"
                    data-unit="{{ $product->unit ?? 'Pcs' }}"
                    data-category="{{ $product->category ?? '' }}"
                    data-brand="{{ $product->brand ?? '' }}"
                    data-barcode="{{ $product->barcode ?? $product->sku ?? '' }}">
                <div class="px-2 py-1.5 text-[11px] font-semibold text-slate-800 line-clamp-2 min-h-[2.25rem] leading-tight">{{ $product->name }}</div>
                <div class="h-[72px] bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center overflow-hidden">
                    @if ($product->imageUrl())
                        <img src="{{ $product->imageUrl() }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                    @else
                        <span class="text-3xl font-black text-slate-200">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="px-2 py-1.5 flex items-center justify-between border-t bg-white text-xs">
                    <span class="font-bold text-emerald-700">{{ $currency }}{{ number_format($product->price, 2) }}</span>
                    <span class="text-slate-400">{{ $product->stock }} pcs</span>
                </div>
            </button>
            @endforeach
        </div>
        @if ($products->isEmpty())
            <p class="text-center text-slate-500 py-8">No products in stock. <a href="{{ route('products.create') }}" class="text-blue-600 underline">Add products</a></p>
        @endif
    </div>

    {{-- Right: Checkout --}}
    <div class="flex-1 flex flex-col bg-white min-h-[50vh] lg:min-h-0">
        <div class="bg-slate-800 text-white px-4 py-2 flex items-center justify-between text-sm">
            <span class="font-semibold">Checkout</span>
            <span class="text-slate-300">{{ $warehouse }} · Tax {{ $taxRate }}%</span>
        </div>
        <form method="POST" action="{{ route('pos.checkout') }}" id="checkout-form" class="flex flex-col flex-1">
            @csrf
            <div class="grid md:grid-cols-3 gap-0 border-b border-slate-200">
                <div class="p-3 border-b md:border-b-0 md:border-r border-slate-200">
                    <label class="text-xs font-semibold text-red-600">Customer Name *</label>
                    <div class="flex gap-1 mt-1">
                        <select name="customer_id" id="customer-select" class="flex-1 rounded border-slate-300 text-sm py-1.5">
                            <option value="">Walk-in customer</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" id="open-customer-modal" class="w-9 h-9 rounded bg-blue-600 text-white text-lg leading-none">+</button>
                    </div>
                </div>
                <div class="p-3 border-b md:border-b-0 md:border-r border-slate-200">
                    <label class="text-xs font-semibold text-red-600">Warehouse *</label>
                    <select name="warehouse" class="w-full mt-1 rounded border-slate-300 text-sm py-1.5">
                        <option value="{{ $warehouse }}">{{ $warehouse }}</option>
                    </select>
                </div>
                <div class="p-3">
                    <label class="text-xs font-semibold text-red-600">Delivery Status *</label>
                    <select name="delivery_status" class="w-full mt-1 rounded border-slate-300 text-sm py-1.5">
                        <option value="delivered">Delivered</option>
                        <option value="pending">Pending</option>
                        <option value="shipped">Shipped</option>
                    </select>
                </div>
            </div>

            <div class="flex-1 overflow-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-700 text-white sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium">Product</th>
                            <th class="px-3 py-2 text-left font-medium">Unit</th>
                            <th class="px-3 py-2 text-right font-medium">Price</th>
                            <th class="px-3 py-2 text-center font-medium w-24">Quantity</th>
                            <th class="px-3 py-2 text-right font-medium">Total</th>
                            <th class="px-3 py-2 w-8"></th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        <tr id="cart-empty-row"><td colspan="6" class="py-16 text-center text-slate-400">Select products from the left panel</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 p-4 bg-slate-50">
                <div class="flex flex-wrap gap-6 justify-end mb-4 text-sm">
                    <div class="text-right"><div class="text-slate-500">Sub Total (Before Discount)</div><div class="font-semibold" id="subtotal">{{ $currency }}0.00</div></div>
                    <div class="text-right"><div class="text-slate-500">Total Tax</div><div class="font-semibold text-red-600" id="total-tax">{{ $currency }}0.00</div></div>
                    <div class="text-right"><div class="text-slate-500">Total Discount</div><div class="font-semibold text-red-600" id="total-discount">{{ $currency }}0.00</div></div>
                    <div class="text-right"><div class="text-slate-500 font-semibold">Grand Total</div><div class="text-xl font-bold" id="grand-total">{{ $currency }}0.00</div></div>
                </div>

                <div class="grid md:grid-cols-3 gap-3 items-end">
                    <div>
                        <label class="text-xs font-medium text-slate-600">Payment Method</label>
                        <select name="payment_method" class="w-full mt-1 rounded border-slate-300 text-sm">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="mobile">Mobile Banking</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600">Reference Number</label>
                        <input type="text" name="payment_reference" class="w-full mt-1 rounded border-slate-300 text-sm" placeholder="Optional">
                    </div>
                    <div class="flex gap-2">
                        <input type="hidden" name="paid_amount" id="paid-amount" value="0">
                        <input type="hidden" name="order_discount" id="order-discount" value="0">
                        <div id="checkout-fields"></div>
                        <button type="submit" id="pay-btn" disabled class="flex-1 py-2.5 rounded bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 text-white font-semibold flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/></svg>
                            Pay
                        </button>
                        <button type="button" id="cancel-btn" class="px-5 py-2.5 rounded bg-red-700 hover:bg-red-600 text-white font-semibold">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<audio id="pos-add-sound" preload="auto" playsinline src="{{ asset('sounds/pos-add.wav?v=2') }}"></audio>

{{-- Manage Customer Modal --}}
<div id="customer-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" id="modal-backdrop"></div>
    <div class="absolute inset-4 md:inset-auto md:top-8 md:left-1/2 md:-translate-x-1/2 md:w-full md:max-w-4xl bg-white rounded-xl shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="text-lg font-semibold text-blue-700">Manage Customer</h3>
            <button type="button" id="close-customer-modal" class="w-8 h-8 rounded-full bg-red-600 text-white">×</button>
        </div>
        <form id="customer-form" class="p-5 grid md:grid-cols-2 gap-4 text-sm">
            @csrf
            <div><label class="text-red-600 font-medium">Customer Name *</label><input name="name" required class="w-full mt-1 rounded border-slate-300"></div>
            <div><label class="font-medium">Contact Person</label><input name="contact_person" class="w-full mt-1 rounded border-slate-300"></div>
            <div><label class="font-medium">Email</label><input type="email" name="email" class="w-full mt-1 rounded border-slate-300"></div>
            <div><label class="font-medium">Website</label><input name="website" class="w-full mt-1 rounded border-slate-300"></div>
            <div><label class="text-red-600 font-medium">Mobile Number *</label><input name="mobile" required class="w-full mt-1 rounded border-slate-300"></div>
            <div><label class="font-medium">Phone Number</label><input name="phone" class="w-full mt-1 rounded border-slate-300"></div>
            <div><label class="font-medium">Tax Number</label><input name="tax_number" class="w-full mt-1 rounded border-slate-300"></div>
            <div class="md:col-span-2 border-t pt-3 font-semibold text-slate-700">Billing Address</div>
            <div class="md:col-span-2"><label class="text-red-600 font-medium">Address *</label><textarea name="address" rows="2" required class="w-full mt-1 rounded border-slate-300"></textarea></div>
            <div><label class="text-red-600 font-medium">Country *</label><input name="billing_country" required class="w-full mt-1 rounded border-slate-300" value="Bangladesh"></div>
            <div><label class="text-red-600 font-medium">City *</label><input name="billing_city" required class="w-full mt-1 rounded border-slate-300"></div>
            <div class="md:col-span-2 flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-lg font-medium">Save</button>
                <button type="button" id="cancel-customer-modal" class="px-6 py-2 bg-red-800 text-white rounded-lg">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const currency = @json($currency);
    const taxRate = @json($taxRate);
    const categories = @json($categories);
    const brands = @json($brands);
    const products = @json($productCatalog);

    let filterMode = 'category';
    let activeFilter = '';
    const cart = new Map();

    const fmt = n => currency + Number(n).toFixed(2);
    const esc = s => String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

    const addSoundEl = document.getElementById('pos-add-sound');
    let soundReady = false;

    function unlockSound() {
        if (soundReady || !addSoundEl) return;
        addSoundEl.volume = 0.001;
        const attempt = addSoundEl.play();
        if (!attempt) {
            soundReady = true;
            return;
        }
        attempt.then(() => {
            addSoundEl.pause();
            addSoundEl.currentTime = 0;
            addSoundEl.volume = 0.9;
            soundReady = true;
        }).catch(() => {});
    }

    function playAddSound() {
        if (!addSoundEl) return;
        unlockSound();
        const clip = addSoundEl.cloneNode(true);
        clip.volume = 0.9;
        clip.play().catch(() => {
            addSoundEl.currentTime = 0;
            addSoundEl.volume = 0.9;
            addSoundEl.play().catch(() => {});
        });
    }

    ['pointerdown', 'keydown'].forEach(evt => {
        document.addEventListener(evt, unlockSound, { once: true, capture: true });
    });

    function renderChips() {
        const list = filterMode === 'category' ? categories : brands;
        const el = document.getElementById('filter-chips');
        el.innerHTML = '<button type="button" data-filter="" class="chip px-2 py-0.5 rounded text-xs ' + (!activeFilter ? 'bg-blue-600 text-white' : 'bg-white border') + '">All</button>';
        list.forEach(v => {
            el.innerHTML += `<button type="button" data-filter="${esc(v)}" class="chip px-2 py-0.5 rounded text-xs ${activeFilter===v?'bg-blue-600 text-white':'bg-white border'}">${esc(v)}</button>`;
        });
        el.querySelectorAll('.chip').forEach(btn => btn.onclick = () => { activeFilter = btn.dataset.filter; renderChips(); filterProducts(); });
    }

    function filterProducts() {
        const q = document.getElementById('search-name').value.toLowerCase();
        document.querySelectorAll('.product-card').forEach(card => {
            const matchSearch = !q || card.dataset.name.toLowerCase().includes(q);
            const val = filterMode === 'category' ? card.dataset.category : card.dataset.brand;
            const matchFilter = !activeFilter || val === activeFilter;
            card.classList.toggle('hidden', !(matchSearch && matchFilter));
        });
    }

    function addProduct(id) {
        const p = products.find(x => x.id == id);
        if (!p) return;
        const cur = cart.get(id) || { ...p, qty: 0, discount: 0 };
        if (cur.qty >= p.stock) return alert('Not enough stock for ' + p.name);
        cur.qty++;
        cart.set(id, cur);
        playAddSound();
        renderCart();
    }

    function renderCart() {
        const body = document.getElementById('cart-body');
        const fields = document.getElementById('checkout-fields');
        const payBtn = document.getElementById('pay-btn');
        fields.innerHTML = '';

        if (cart.size === 0) {
            body.innerHTML = '<tr id="cart-empty-row"><td colspan="6" class="py-16 text-center text-slate-400">Select products from the left panel</td></tr>';
            ['subtotal','total-tax','total-discount','grand-total'].forEach(id => document.getElementById(id).textContent = fmt(0));
            document.getElementById('paid-amount').value = 0;
            payBtn.disabled = true;
            return;
        }

        let subtotal = 0, totalTax = 0, totalDiscount = 0, i = 0;
        body.innerHTML = '';

        cart.forEach(item => {
            const lineSub = item.price * item.qty;
            const taxable = Math.max(lineSub - item.discount, 0);
            const lineTax = taxable * (taxRate / 100);
            const lineTotal = taxable + lineTax;
            subtotal += lineSub;
            totalDiscount += item.discount;
            totalTax += lineTax;

            body.innerHTML += `<tr class="border-b"><td class="px-3 py-2">${esc(item.name)}</td><td class="px-3 py-2">${esc(item.unit)}</td><td class="px-3 py-2 text-right">${fmt(item.price)}</td><td class="px-3 py-2 text-center"><input type="number" min="1" max="${item.stock}" value="${item.qty}" data-qty="${item.id}" class="w-16 text-center rounded border-slate-300 text-sm"></td><td class="px-3 py-2 text-right font-medium">${fmt(lineTotal)}</td><td class="px-3 py-2"><button type="button" data-remove="${item.id}" class="text-red-600">×</button></td></tr>`;

            fields.innerHTML += `<input type="hidden" name="items[${i}][product_id]" value="${item.id}"><input type="hidden" name="items[${i}][quantity]" value="${item.qty}"><input type="hidden" name="items[${i}][discount]" value="${item.discount}">`;
            i++;
        });

        const grand = subtotal - totalDiscount + totalTax;
        document.getElementById('subtotal').textContent = fmt(subtotal);
        document.getElementById('total-tax').textContent = fmt(totalTax);
        document.getElementById('total-discount').textContent = fmt(totalDiscount);
        document.getElementById('grand-total').textContent = fmt(grand);
        document.getElementById('paid-amount').value = grand.toFixed(2);
        payBtn.disabled = false;

        body.querySelectorAll('[data-qty]').forEach(inp => inp.onchange = () => {
            const item = cart.get(+inp.dataset.qty);
            item.qty = Math.min(Math.max(+inp.value, 1), item.stock);
            cart.set(+inp.dataset.qty, item);
            renderCart();
        });
        body.querySelectorAll('[data-remove]').forEach(btn => btn.onclick = () => { cart.delete(+btn.dataset.remove); renderCart(); });
    }

    document.querySelectorAll('.product-card').forEach(c => c.onclick = () => { unlockSound(); addProduct(+c.dataset.id); });
    document.getElementById('search-name').oninput = filterProducts;
    document.getElementById('scan-barcode').onkeydown = e => {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const code = e.target.value.trim().toLowerCase();
        const p = products.find(x => (x.barcode||'').toLowerCase() === code || String(x.id) === code);
        if (p) { addProduct(p.id); e.target.value = ''; }
        else alert('Product not found');
    };
    document.getElementById('tab-category').onclick = () => {
        filterMode='category'; activeFilter='';
        document.getElementById('tab-category').className = 'filter-tab flex-1 py-2.5 text-sm font-bold bg-[#2563eb] text-white';
        document.getElementById('tab-brand').className = 'filter-tab flex-1 py-2.5 text-sm font-bold bg-[#1e4d3a] text-white/70';
        renderChips(); filterProducts();
    };
    document.getElementById('tab-brand').onclick = () => {
        filterMode='brand'; activeFilter='';
        document.getElementById('tab-brand').className = 'filter-tab flex-1 py-2.5 text-sm font-bold bg-[#1e4d3a] text-white';
        document.getElementById('tab-category').className = 'filter-tab flex-1 py-2.5 text-sm font-bold bg-[#2563eb] text-white/70';
        renderChips(); filterProducts();
    };
    document.getElementById('cancel-btn').onclick = () => { cart.clear(); renderCart(); };
    renderChips();

    const modal = document.getElementById('customer-modal');
    document.getElementById('open-customer-modal').onclick = () => modal.classList.remove('hidden');
    ['close-customer-modal','cancel-customer-modal','modal-backdrop'].forEach(id => document.getElementById(id).onclick = () => modal.classList.add('hidden'));

    document.getElementById('customer-form').onsubmit = async e => {
        e.preventDefault();
        const form = e.target;
        const res = await fetch(@json(route('customers.store')), {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
        });
        if (!res.ok) return alert('Could not save customer');
        const data = await res.json();
        const sel = document.getElementById('customer-select');
        sel.innerHTML += `<option value="${data.id}" selected>${esc(data.name)}</option>`;
        modal.classList.add('hidden');
        form.reset();
    };
})();
</script>
@endpush
