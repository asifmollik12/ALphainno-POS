@extends('layouts.app')

@section('title', 'Sell')

@section('content')
<div class="mb-4">
    <h1 class="text-xl font-semibold text-white">Point of Sale</h1>
    <p class="text-slate-400 text-sm mt-0.5">Tap products to add them to the cart</p>
</div>

@if ($products->isEmpty())
    <div class="text-center py-16 border border-dashed border-slate-700 rounded-xl">
        <p class="text-slate-400 mb-4">No products in stock. Add products first.</p>
        <a href="{{ route('products.create') }}" class="text-emerald-400 hover:text-emerald-300 text-sm">Add products →</a>
    </div>
@else
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-3">
        <input type="search" id="search" placeholder="Search products..."
               class="w-full rounded-lg bg-slate-900 border border-slate-700 px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">

        <div id="product-grid" class="grid sm:grid-cols-2 gap-3 max-h-[65vh] overflow-y-auto pr-1">
            @foreach ($products as $product)
            <button type="button"
                    class="product-card text-left bg-slate-900 border border-slate-800 hover:border-emerald-600 rounded-xl p-4 transition"
                    data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                    data-price="{{ $product->price }}"
                    data-stock="{{ $product->stock }}">
                <div class="font-medium text-white">{{ $product->name }}</div>
                <div class="flex justify-between items-center mt-2 text-sm">
                    <span class="text-emerald-400 font-semibold">৳{{ number_format($product->price, 2) }}</span>
                    <span class="text-slate-500">Stock: {{ $product->stock }}</span>
                </div>
            </button>
            @endforeach
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex flex-col min-h-[400px]">
        <h2 class="font-semibold text-white mb-3">Cart</h2>

        <div id="cart-empty" class="flex-1 flex items-center justify-center text-slate-500 text-sm">
            Cart is empty
        </div>

        <ul id="cart-items" class="flex-1 space-y-2 overflow-y-auto hidden"></ul>

        <div id="cart-footer" class="border-t border-slate-800 pt-4 mt-4 hidden">
            <div class="flex justify-between text-lg font-semibold mb-4">
                <span>Total</span>
                <span id="cart-total" class="text-emerald-400">৳0.00</span>
            </div>

            <form method="POST" action="{{ route('pos.checkout') }}" id="checkout-form">
                @csrf
                <div id="checkout-fields"></div>
                <button type="submit" id="checkout-btn"
                        class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-medium py-3 rounded-lg">
                    Complete sale
                </button>
            </form>
        </div>
    </div>
</div>
@endif

@if ($products->isNotEmpty())
<script>
(() => {
    const cart = new Map();
    const search = document.getElementById('search');
    const cartItems = document.getElementById('cart-items');
    const cartEmpty = document.getElementById('cart-empty');
    const cartFooter = document.getElementById('cart-footer');
    const cartTotal = document.getElementById('cart-total');
    const checkoutFields = document.getElementById('checkout-fields');
    const checkoutBtn = document.getElementById('checkout-btn');

    search?.addEventListener('input', () => {
        const q = search.value.toLowerCase();
        document.querySelectorAll('.product-card').forEach(el => {
            const name = el.dataset.name.toLowerCase();
            el.classList.toggle('hidden', q && !name.includes(q));
        });
    });

    document.querySelectorAll('.product-card').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const stock = parseInt(btn.dataset.stock, 10);
            const current = cart.get(id)?.qty || 0;

            if (current >= stock) {
                alert('Not enough stock for ' + btn.dataset.name);
                return;
            }

            cart.set(id, {
                id,
                name: btn.dataset.name,
                price: parseFloat(btn.dataset.price),
                stock,
                qty: current + 1,
            });

            render();
        });
    });

    function render() {
        if (cart.size === 0) {
            cartItems.classList.add('hidden');
            cartEmpty.classList.remove('hidden');
            cartFooter.classList.add('hidden');
            checkoutFields.innerHTML = '';
            return;
        }

        cartEmpty.classList.add('hidden');
        cartItems.classList.remove('hidden');
        cartFooter.classList.remove('hidden');

        let total = 0;
        cartItems.innerHTML = '';
        checkoutFields.innerHTML = '';
        let i = 0;

        cart.forEach(item => {
            const subtotal = item.price * item.qty;
            total += subtotal;

            const li = document.createElement('li');
            li.className = 'flex items-center justify-between gap-2 bg-slate-950 rounded-lg px-3 py-2 text-sm';
            li.innerHTML = `
                <div class="min-w-0">
                    <div class="text-white truncate">${item.name}</div>
                    <div class="text-slate-500">৳${item.price.toFixed(2)} × ${item.qty}</div>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button type="button" data-action="dec" data-id="${item.id}" class="w-7 h-7 rounded bg-slate-800 hover:bg-slate-700">−</button>
                    <span class="w-6 text-center">${item.qty}</span>
                    <button type="button" data-action="inc" data-id="${item.id}" class="w-7 h-7 rounded bg-slate-800 hover:bg-slate-700">+</button>
                    <button type="button" data-action="remove" data-id="${item.id}" class="ml-1 text-red-400 hover:text-red-300 text-xs">✕</button>
                </div>
            `;
            cartItems.appendChild(li);

            checkoutFields.innerHTML += `
                <input type="hidden" name="items[${i}][product_id]" value="${item.id}">
                <input type="hidden" name="items[${i}][quantity]" value="${item.qty}">
            `;
            i++;
        });

        cartTotal.textContent = '৳' + total.toFixed(2);

        cartItems.querySelectorAll('button').forEach(b => {
            b.addEventListener('click', () => {
                const id = b.dataset.id;
                const item = cart.get(id);
                if (!item) return;

                if (b.dataset.action === 'remove') {
                    cart.delete(id);
                } else if (b.dataset.action === 'inc') {
                    if (item.qty >= item.stock) {
                        alert('Not enough stock');
                        return;
                    }
                    item.qty++;
                } else if (b.dataset.action === 'dec') {
                    item.qty--;
                    if (item.qty <= 0) cart.delete(id);
                }
                render();
            });
        });
    }
})();
</script>
@endif
@endsection
