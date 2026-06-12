@extends('layouts.dashboard')

@section('pos_fullscreen', true)

@section('title', 'POS')

@php
    $currency = $setting->currency ?? '৳';
    $warehouse = $setting->warehouse_name ?? 'Main Warehouse';
    $taxRate = (float) ($setting->default_tax_rate ?? 0);
@endphp

@section('content')
@push('head')
<style>
    .pos-product-card { display: flex; flex-direction: column; height: 100%; }
    .pos-product-thumb {
        position: relative;
        width: 100%;
        padding-top: 100%;
        flex-shrink: 0;
        background: linear-gradient(135deg, rgba(224,242,254,.55), rgba(241,245,249,.95));
        overflow: hidden;
    }
    .pos-product-thumb-inner {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
    }
    .pos-product-thumb-inner img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
    }
    .pos-product-meta { flex: 1; display: flex; flex-direction: column; min-height: 4.25rem; }
</style>
@endpush
<div class="flex flex-col lg:flex-row h-[calc(100vh-0px)] bg-ai-mist">
    {{-- Left: Product picker --}}
    <div class="lg:w-[42%] xl:w-[38%] bg-ai-sky/40 border-r border-ai-grey/60 p-3 flex flex-col min-h-[50vh] lg:min-h-0">
        <div class="bg-white rounded-md border border-ai-grey/80 p-3 shadow-sm mb-2">
            <div class="flex mb-2 overflow-hidden rounded-md">
                <button type="button" id="tab-category" class="filter-tab flex-1 py-2.5 text-sm font-bold bg-ai-cyan text-white">Category</button>
                <button type="button" id="tab-brand" class="filter-tab flex-1 py-2.5 text-sm font-bold bg-ai-navy text-white/60">Brand</button>
            </div>
            <input type="text" id="search-name" placeholder="Search By Name" class="w-full mb-2 rounded border-ai-grey text-sm py-2 px-3 focus:border-ai-purple focus:ring-ai-purple/30">
            <input type="text" id="scan-barcode" placeholder="Scan Barcode" autofocus class="w-full rounded border-2 border-ai-cyan text-sm py-2 px-3 focus:ring-2 focus:ring-ai-cyan/40 outline-none">
        </div>

        <div id="filter-chips" class="flex flex-wrap gap-1.5 mb-2 min-h-[24px]"></div>

        <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-3 overflow-y-auto flex-1 pr-1 pb-2 auto-rows-fr">
            @foreach ($products as $product)
            @php $initial = strtoupper(substr($product->name, 0, 1)); @endphp
            <button type="button"
                    @disabled($product->stock <= 0)
                    class="product-card pos-product-card group bg-white border border-ai-grey/80 rounded-lg overflow-hidden text-left transition-all {{ $product->stock <= 0 ? 'opacity-50 cursor-not-allowed grayscale' : 'hover:shadow-lg hover:border-ai-cyan active:scale-[0.98]' }}"
                    data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                    data-price="{{ $product->price }}"
                    data-stock="{{ $product->stock }}"
                    data-unit="{{ $product->unit ?? 'Pcs' }}"
                    data-category="{{ $product->category ?? '' }}"
                    data-brand="{{ $product->brand ?? '' }}"
                    data-barcode="{{ $product->barcode ?? $product->sku ?? '' }}"
                    data-out-of-stock="{{ $product->stock <= 0 ? '1' : '0' }}">
                <div class="pos-product-thumb">
                    <div class="pos-product-thumb-inner">
                        @if ($product->imageUrl())
                            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <span class="hidden w-full h-full items-center justify-center text-4xl font-black text-ai-grey/60">{{ $initial }}</span>
                        @else
                            <span class="text-4xl font-black text-ai-grey/60">{{ $initial }}</span>
                        @endif
                    </div>
                    @if ($product->stock <= 0)
                        <span class="absolute inset-x-0 bottom-0 bg-red-600/90 text-white text-[10px] font-bold text-center py-1 z-10">Out of stock</span>
                    @endif
                </div>
                <div class="pos-product-meta px-2.5 py-2">
                    <div class="text-[11px] font-semibold text-slate-800 line-clamp-2 leading-snug flex-1">
                        {{ $product->name }}
                    </div>
                    <div class="flex items-center justify-between mt-auto pt-1.5 border-t border-slate-100 text-xs">
                        <span class="font-bold text-ai-purple">{{ $currency }}{{ number_format($product->price, 2) }}</span>
                        <span class="text-slate-400">{{ $product->stock }} pcs</span>
                    </div>
                </div>
            </button>
            @endforeach
        </div>
        @if ($products->isEmpty())
            <p class="text-center text-slate-500 py-8">No products in stock. <a href="{{ route('products.create') }}" class="text-ai-purple underline">Add products</a></p>
        @endif
    </div>

    {{-- Right: Checkout --}}
    <div class="flex-1 flex flex-col bg-white min-h-[50vh] lg:min-h-0">
        <div class="bg-ai-navy text-white px-4 py-2 flex items-center justify-between text-sm">
            <span class="font-semibold">Checkout</span>
            <span class="text-blue-300">{{ $warehouse }} · Tax {{ $taxRate }}%</span>
        </div>
        <form method="POST" action="{{ route('pos.checkout') }}" id="checkout-form" class="flex flex-col flex-1">
            @csrf
            <div class="grid md:grid-cols-3 gap-0 border-b border-slate-200">
                <div class="p-3 border-b md:border-b-0 md:border-r border-slate-200">
                    <label class="text-xs font-semibold text-ai-purple">Customer Name *</label>
                    <div class="flex gap-1 mt-1">
                        <select name="customer_id" id="customer-select" class="flex-1 rounded border-ai-grey text-sm py-1.5">
                            <option value="">Walk-in customer</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" id="open-customer-modal" class="w-9 h-9 rounded bg-ai-purple hover:bg-violet-500 text-white text-lg leading-none">+</button>
                    </div>
                </div>
                <div class="p-3 border-b md:border-b-0 md:border-r border-slate-200">
                    <label class="text-xs font-semibold text-ai-navy">Warehouse *</label>
                    <select name="warehouse" class="w-full mt-1 rounded border-slate-300 text-sm py-1.5">
                        <option value="{{ $warehouse }}">{{ $warehouse }}</option>
                    </select>
                </div>
                <div class="p-3">
                    <label class="text-xs font-semibold text-ai-navy">Delivery Status *</label>
                    <select name="delivery_status" class="w-full mt-1 rounded border-slate-300 text-sm py-1.5">
                        <option value="delivered">Delivered</option>
                        <option value="pending">Pending</option>
                        <option value="shipped">Shipped</option>
                    </select>
                </div>
            </div>

            <div class="flex-1 overflow-auto">
                <table class="w-full text-sm">
                    <thead class="bg-ai-navy text-white sticky top-0">
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

            <div class="border-t border-ai-grey/60 p-4 bg-ai-mist/50">
                <div class="flex flex-wrap gap-6 justify-end mb-4 text-sm">
                    <div class="text-right"><div class="text-slate-500">Sub Total (Before Discount)</div><div class="font-semibold" id="subtotal">{{ $currency }}0.00</div></div>
                    <div class="text-right"><div class="text-slate-500">Total Tax</div><div class="font-semibold text-ai-purple" id="total-tax">{{ $currency }}0.00</div></div>
                    <div class="text-right"><div class="text-slate-500">Total Discount</div><div class="font-semibold text-ai-cyan" id="total-discount">{{ $currency }}0.00</div></div>
                    <div class="text-right"><div class="text-slate-500 font-semibold">Grand Total</div><div class="text-xl font-bold text-ai-navy" id="grand-total">{{ $currency }}0.00</div></div>
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
                        <button type="submit" id="pay-btn" disabled class="flex-1 py-2.5 rounded bg-gradient-to-r from-ai-cyan to-ai-purple hover:opacity-90 disabled:opacity-40 text-white disabled:text-slate-400 font-semibold flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/></svg>
                            Pay
                        </button>
                        <button type="button" id="cancel-btn" class="px-5 py-2.5 rounded bg-ai-navy/80 hover:bg-ai-navy text-white font-semibold">Cancel</button>
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
            <h3 class="text-lg font-semibold text-ai-purple">Manage Customer</h3>
            <button type="button" id="close-customer-modal" class="w-8 h-8 rounded-full bg-ai-navy text-white">×</button>
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
                <button type="submit" class="px-6 py-2 bg-ai-cyan text-white rounded-lg font-medium">Save</button>
                <button type="button" id="cancel-customer-modal" class="px-6 py-2 bg-ai-navy text-white rounded-lg">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Alert popup --}}
<div id="pos-alert-modal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50" id="pos-alert-backdrop"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-2xl p-6 max-w-sm w-[90%]">
        <h3 class="text-lg font-bold text-ai-navy mb-2">Notice</h3>
        <p id="pos-alert-message" class="text-slate-600 text-sm mb-5 leading-relaxed"></p>
        <button type="button" id="pos-alert-ok" class="w-full py-2.5 bg-ai-navy text-white rounded-lg font-semibold">OK</button>
    </div>
</div>

{{-- Pay confirm popup --}}
<div id="pos-confirm-modal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50" id="pos-confirm-backdrop"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-2xl p-6 max-w-sm w-[90%]">
        <h3 class="text-lg font-bold text-ai-navy mb-2">Confirm Payment</h3>
        <p id="pos-confirm-message" class="text-slate-600 text-sm mb-5 leading-relaxed"></p>
        <div class="flex gap-3">
            <button type="button" id="pos-confirm-cancel" class="flex-1 py-2.5 rounded-lg border border-ai-grey text-slate-700 font-semibold">Cancel</button>
            <button type="button" id="pos-confirm-ok" class="flex-1 py-2.5 rounded-lg bg-gradient-to-r from-ai-cyan to-ai-purple text-white font-semibold">Confirm Pay</button>
        </div>
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
            addSoundEl.volume = 1;
            soundReady = true;
        }).catch(() => {});
    }

    function playAddSound() {
        if (!addSoundEl) return;
        unlockSound();
        const clip = addSoundEl.cloneNode(true);
        clip.volume = 1;
        clip.play().catch(() => {
            addSoundEl.currentTime = 0;
            addSoundEl.volume = 1;
            addSoundEl.play().catch(() => {});
        });
    }

    ['pointerdown', 'keydown'].forEach(evt => {
        document.addEventListener(evt, unlockSound, { once: true, capture: true });
    });

    function showPosAlert(message) {
        const modal = document.getElementById('pos-alert-modal');
        document.getElementById('pos-alert-message').textContent = message;
        modal.classList.remove('hidden');
        return new Promise(resolve => {
            const close = () => {
                modal.classList.add('hidden');
                document.getElementById('pos-alert-ok').onclick = null;
                document.getElementById('pos-alert-backdrop').onclick = null;
                resolve();
            };
            document.getElementById('pos-alert-ok').onclick = close;
            document.getElementById('pos-alert-backdrop').onclick = close;
        });
    }

    function showPosConfirm(message) {
        const modal = document.getElementById('pos-confirm-modal');
        document.getElementById('pos-confirm-message').textContent = message;
        modal.classList.remove('hidden');
        return new Promise(resolve => {
            const cleanup = (result) => {
                modal.classList.add('hidden');
                document.getElementById('pos-confirm-ok').onclick = null;
                document.getElementById('pos-confirm-cancel').onclick = null;
                document.getElementById('pos-confirm-backdrop').onclick = null;
                resolve(result);
            };
            document.getElementById('pos-confirm-ok').onclick = () => cleanup(true);
            document.getElementById('pos-confirm-cancel').onclick = () => cleanup(false);
            document.getElementById('pos-confirm-backdrop').onclick = () => cleanup(false);
        });
    }

    function stockError(name) {
        return (name || 'Product') + ' — ei product stock e nei';
    }

    function validateCartStock() {
        for (const [id, item] of cart) {
            const p = products.find(x => x.id == id);
            if (!p || p.stock <= 0 || item.qty > p.stock) {
                return stockError(p?.name || item.name);
            }
        }
        return null;
    }

    const TAB_CYAN_ON = 'filter-tab flex-1 py-2.5 text-sm font-bold bg-ai-cyan text-white';
    const TAB_CYAN_OFF = 'filter-tab flex-1 py-2.5 text-sm font-bold bg-ai-navy text-white/60';
    const TAB_PURPLE_ON = 'filter-tab flex-1 py-2.5 text-sm font-bold bg-ai-purple text-white';
    const TAB_PURPLE_OFF = 'filter-tab flex-1 py-2.5 text-sm font-bold bg-ai-navy text-white/60';

    function chipActiveClass() {
        return filterMode === 'category' ? 'bg-ai-cyan text-white' : 'bg-ai-purple text-white';
    }

    function renderChips() {
        const list = filterMode === 'category' ? categories : brands;
        const el = document.getElementById('filter-chips');
        const active = chipActiveClass();
        el.innerHTML = '<button type="button" data-filter="" class="chip px-2 py-0.5 rounded text-xs ' + (!activeFilter ? active : 'bg-white border border-ai-grey') + '">All</button>';
        list.forEach(v => {
            el.innerHTML += `<button type="button" data-filter="${esc(v)}" class="chip px-2 py-0.5 rounded text-xs ${activeFilter===v?active:'bg-white border border-ai-grey'}">${esc(v)}</button>`;
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
        if (p.stock <= 0) return showPosAlert(stockError(p.name));
        const cur = cart.get(id) || { ...p, qty: 0, discount: 0 };
        if (cur.qty >= p.stock) return showPosAlert(stockError(p.name));
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
            const p = products.find(x => x.id == item.id);
            if (!p || p.stock <= 0) {
                cart.delete(+inp.dataset.qty);
                showPosAlert(stockError(p?.name || item.name));
                renderCart();
                return;
            }
            if (+inp.value > p.stock) {
                showPosAlert(stockError(p.name));
                inp.value = p.stock;
            }
            item.qty = Math.min(Math.max(+inp.value, 1), p.stock);
            cart.set(+inp.dataset.qty, item);
            renderCart();
        });
        body.querySelectorAll('[data-remove]').forEach(btn => btn.onclick = () => { cart.delete(+btn.dataset.remove); renderCart(); });
    }

    document.querySelectorAll('.product-card').forEach(c => {
        c.onclick = () => {
            if (c.dataset.outOfStock === '1' || +c.dataset.stock <= 0) {
                return showPosAlert(stockError(c.dataset.name));
            }
            unlockSound();
            addProduct(+c.dataset.id);
        };
    });
    document.getElementById('search-name').oninput = filterProducts;
    document.getElementById('scan-barcode').onkeydown = e => {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const code = e.target.value.trim().toLowerCase();
        const p = products.find(x => (x.barcode||'').toLowerCase() === code || String(x.id) === code);
        if (!p) return showPosAlert('Product khujte pawa jay ni');
        if (p.stock <= 0) return showPosAlert(stockError(p.name));
        addProduct(p.id);
        e.target.value = '';
    };
    document.getElementById('tab-category').onclick = () => {
        filterMode='category'; activeFilter='';
        document.getElementById('tab-category').className = TAB_CYAN_ON;
        document.getElementById('tab-brand').className = TAB_PURPLE_OFF;
        renderChips(); filterProducts();
    };
    document.getElementById('tab-brand').onclick = () => {
        filterMode='brand'; activeFilter='';
        document.getElementById('tab-brand').className = TAB_PURPLE_ON;
        document.getElementById('tab-category').className = TAB_CYAN_OFF;
        renderChips(); filterProducts();
    };
    document.getElementById('cancel-btn').onclick = () => { cart.clear(); renderCart(); };
    renderChips();

    let allowCheckoutSubmit = false;
    document.getElementById('checkout-form').addEventListener('submit', async e => {
        if (allowCheckoutSubmit) return;
        e.preventDefault();
        if (cart.size === 0) return;

        const stockErr = validateCartStock();
        if (stockErr) return showPosAlert(stockErr);

        const grand = document.getElementById('grand-total').textContent;
        const confirmed = await showPosConfirm('Grand Total: ' + grand + '\n\nPayment confirm korben?');
        if (!confirmed) return;

        allowCheckoutSubmit = true;
        e.target.requestSubmit();
    });

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
