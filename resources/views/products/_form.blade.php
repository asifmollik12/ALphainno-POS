<form method="POST" action="{{ $action }}" class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-4">
    @csrf
    @if ($product)
        @method('PUT')
    @endif

    <div>
        <label for="name" class="block text-sm text-slate-300 mb-1">Product name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $product?->name) }}" required
               class="w-full rounded-lg bg-slate-950 border border-slate-700 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>

    <div>
        <label for="sku" class="block text-sm text-slate-300 mb-1">SKU (optional)</label>
        <input type="text" name="sku" id="sku" value="{{ old('sku', $product?->sku) }}"
               class="w-full rounded-lg bg-slate-950 border border-slate-700 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="price" class="block text-sm text-slate-300 mb-1">Price (৳)</label>
            <input type="number" name="price" id="price" step="0.01" min="0"
                   value="{{ old('price', $product?->price ?? '0') }}" required
                   class="w-full rounded-lg bg-slate-950 border border-slate-700 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>
        <div>
            <label for="stock" class="block text-sm text-slate-300 mb-1">Stock</label>
            <input type="number" name="stock" id="stock" min="0"
                   value="{{ old('stock', $product?->stock ?? '0') }}" required
                   class="w-full rounded-lg bg-slate-950 border border-slate-700 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>
    </div>

    <div class="flex gap-3 pt-2">
        <button type="submit"
                class="bg-emerald-600 hover:bg-emerald-500 text-white font-medium px-5 py-2 rounded-lg">
            {{ $product ? 'Save changes' : 'Add product' }}
        </button>
        <a href="{{ route('products.index') }}" class="text-slate-400 hover:text-white px-3 py-2">Cancel</a>
    </div>
</form>
