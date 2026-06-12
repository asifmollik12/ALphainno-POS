<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-slate-200 p-6 space-y-4 max-w-2xl shadow-sm">
    @csrf
    @if ($product) @method('PUT') @endif
    <div class="grid sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Product name</label>
            <input name="name" value="{{ old('name', $product?->name) }}" required class="w-full rounded-lg border-slate-300 focus:border-violet-500 focus:ring-violet-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">SKU</label>
            <input name="sku" value="{{ old('sku', $product?->sku) }}" class="w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Barcode</label>
            <input name="barcode" value="{{ old('barcode', $product?->barcode) }}" class="w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
            <input name="category" value="{{ old('category', $product?->category) }}" class="w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Brand</label>
            <input name="brand" value="{{ old('brand', $product?->brand) }}" class="w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Unit</label>
            <input name="unit" value="{{ old('unit', $product?->unit ?? 'Pcs') }}" class="w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Cost price</label>
            <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $product?->cost_price ?? 0) }}" class="w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Sale price</label>
            <input type="number" step="0.01" name="price" value="{{ old('price', $product?->price ?? 0) }}" required class="w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Stock</label>
            <input type="number" name="stock" value="{{ old('stock', $product?->stock ?? 0) }}" required class="w-full rounded-lg border-slate-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Min stock alert</label>
            <input type="number" name="min_stock" value="{{ old('min_stock', $product?->min_stock ?? 5) }}" class="w-full rounded-lg border-slate-300">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Product photo</label>
            @if ($product?->imageUrl())
                <img src="{{ $product->imageUrl() }}" alt="" class="h-24 w-24 object-cover rounded-lg border mb-2">
            @endif
            <input type="file" name="image" accept="image/*" class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-violet-50 file:text-violet-700">
        </div>
    </div>
    <div class="flex gap-3 pt-2">
        <button class="px-5 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-500">{{ $product ? 'Save' : 'Add product' }}</button>
        <a href="{{ route('products.index') }}" class="px-3 py-2 text-slate-500">Cancel</a>
    </div>
</form>
