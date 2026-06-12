@php
    $categories = $categories ?? collect();
    $brands = $brands ?? collect();
    $uomOptions = $uomOptions ?? ['pc', 'Pcs', 'kg', 'gm'];
    $discountOptions = $discountOptions ?? [0, 5, 10, 15, 20];
    $taxOptions = $taxOptions ?? [0, 5, 7.5, 10, 15];
    $selectedCategory = old('category', $product?->category);
    $selectedBrand = old('brand', $product?->brand);
    $selectedUnit = old('unit', $product?->unit ?? 'pc');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data"
      class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden"
      x-data="productForm(@json($categories->values()), @json($brands->values()))">
    @csrf
    @if ($product) @method('PUT') @endif

    <div class="px-6 py-5 border-b border-slate-100">
        <h2 class="text-lg font-bold text-slate-900">{{ $product ? 'Update Product' : 'Create Product' }}</h2>
    </div>

    <div class="p-6 space-y-5 max-w-4xl">
        {{-- Name --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Name <span class="text-red-500">*</span></label>
            <input name="name" value="{{ old('name', $product?->name) }}" required
                   class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-ai-purple focus:ring-2 focus:ring-ai-purple/15 outline-none">
        </div>

        {{-- Subcategory & Brand --}}
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-sm font-medium text-slate-700">Subcategory</label>
                    <button type="button" @click="addOption('category')" class="w-6 h-6 rounded bg-ai-navy text-white text-sm leading-none hover:bg-slate-800" title="Add subcategory">+</button>
                </div>
                <select name="category" x-ref="categorySelect" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-ai-purple focus:ring-2 focus:ring-ai-purple/15 outline-none">
                    <option value="">Select subcategory</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" @selected($selectedCategory === $cat)>{{ $cat }}</option>
                    @endforeach
                    @if ($selectedCategory && ! $categories->contains($selectedCategory))
                        <option value="{{ $selectedCategory }}" selected>{{ $selectedCategory }}</option>
                    @endif
                </select>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-sm font-medium text-slate-700">Brand</label>
                    <button type="button" @click="addOption('brand')" class="w-6 h-6 rounded bg-ai-navy text-white text-sm leading-none hover:bg-slate-800" title="Add brand">+</button>
                </div>
                <select name="brand" x-ref="brandSelect" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-ai-purple focus:ring-2 focus:ring-ai-purple/15 outline-none">
                    <option value="">Select Brand</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand }}" @selected($selectedBrand === $brand)>{{ $brand }}</option>
                    @endforeach
                    @if ($selectedBrand && ! $brands->contains($selectedBrand))
                        <option value="{{ $selectedBrand }}" selected>{{ $selectedBrand }}</option>
                    @endif
                </select>
            </div>
        </div>

        {{-- UoM & UoM Value --}}
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">UoM</label>
                <select name="unit" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-ai-purple focus:ring-2 focus:ring-ai-purple/15 outline-none">
                    @foreach ($uomOptions as $uom)
                        <option value="{{ $uom }}" @selected($selectedUnit === $uom)>{{ $uom }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">UoM Value</label>
                <input type="number" step="0.01" name="uom_value" value="{{ old('uom_value', $product?->uom_value ?? 0) }}"
                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-ai-purple focus:ring-2 focus:ring-ai-purple/15 outline-none">
            </div>
        </div>

        {{-- Sale Price & Reorder --}}
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Sale Price</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $product?->price ?? 0) }}" required
                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-ai-purple focus:ring-2 focus:ring-ai-purple/15 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Reorder Quantity</label>
                <input type="number" name="min_stock" value="{{ old('min_stock', $product?->min_stock ?? 0) }}"
                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-ai-purple focus:ring-2 focus:ring-ai-purple/15 outline-none">
            </div>
        </div>

        {{-- SKU & Thumbnail --}}
        <div class="grid sm:grid-cols-2 gap-5 items-start">
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">SKU No <span class="text-red-500">*</span></label>
                    <input name="sku" value="{{ old('sku', $product?->sku) }}" required
                           class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-ai-purple focus:ring-2 focus:ring-ai-purple/15 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Upload Thumbnail Image</label>
                    @php $existingImage = $product?->imageUrl(); @endphp
                    <label for="product-image-input" class="product-thumb-upload flex flex-col items-center justify-center w-full max-w-[180px] rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 hover:bg-slate-100 cursor-pointer overflow-hidden relative" style="aspect-ratio: 1 / 1;">
                        <input type="file" id="product-image-input" name="image" accept="image/*" class="sr-only">
                        <div id="product-upload-placeholder" class="text-center p-4 {{ $existingImage ? 'hidden' : '' }}">
                            <span class="inline-flex w-10 h-10 items-center justify-center rounded-full bg-white border border-slate-200 text-xl text-slate-400 mb-2">+</span>
                            <span class="block text-sm text-slate-500">Upload</span>
                        </div>
                        @if ($existingImage)
                            <img id="product-existing-image" src="{{ $existingImage }}" alt="{{ $product->name }}" class="absolute inset-0 w-full h-full object-contain p-2 bg-white">
                        @endif
                        <img id="product-new-preview" alt="" class="hidden absolute inset-0 w-full h-full object-contain p-2 bg-white">
                    </label>
                    <p class="mt-1.5 text-xs text-slate-400">JPG, PNG or WebP · max 2MB</p>
                </div>
            </div>
            <div class="grid sm:grid-cols-1 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Discount</label>
                    <select name="discount_rate" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-ai-purple focus:ring-2 focus:ring-ai-purple/15 outline-none">
                        <option value="">Select discount</option>
                        @foreach ($discountOptions as $rate)
                            <option value="{{ $rate }}" @selected((string) old('discount_rate', $product?->discount_rate ?? '') === (string) $rate)>{{ $rate }}%</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Product Vat/Tax</label>
                    <select name="tax_rate" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-ai-purple focus:ring-2 focus:ring-ai-purple/15 outline-none">
                        <option value="">Select Vat/Tax type</option>
                        @foreach ($taxOptions as $rate)
                            <option value="{{ $rate }}" @selected((string) old('tax_rate', $product?->tax_rate ?? '') === (string) $rate)>{{ $rate }}%</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Inventory extras --}}
        <details class="rounded-lg border border-slate-200 bg-slate-50/50 open:bg-white">
            <summary class="cursor-pointer px-4 py-3 text-sm font-medium text-slate-700">Inventory &amp; pricing details</summary>
            <div class="px-4 pb-4 pt-1 grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Purchase Price</label>
                    <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $product?->cost_price ?? 0) }}"
                           class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Stock Quantity</label>
                    <input type="number" name="stock" value="{{ old('stock', $product?->stock ?? 0) }}" required
                           class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Barcode</label>
                    <input name="barcode" value="{{ old('barcode', $product?->barcode) }}"
                           class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm">
                </div>
            </div>
        </details>
    </div>

    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-wrap gap-3">
        <button type="submit" class="px-6 py-2.5 rounded-lg bg-ai-navy hover:bg-slate-900 text-white text-sm font-semibold">
            {{ $product ? 'Update Product' : 'Create Product' }}
        </button>
        <a href="{{ route('products.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50">Cancel</a>
    </div>
</form>

@once
@push('scripts')
<script>
function productForm(categories, brands) {
    return {
        addOption(type) {
            const label = type === 'category' ? 'New subcategory name' : 'New brand name';
            const value = prompt(label);
            if (!value?.trim()) return;
            const select = type === 'category' ? this.$refs.categorySelect : this.$refs.brandSelect;
            const opt = document.createElement('option');
            opt.value = value.trim();
            opt.textContent = value.trim();
            opt.selected = true;
            select.appendChild(opt);
        },
    };
}

document.getElementById('product-image-input')?.addEventListener('change', function (e) {
    const file = e.target.files?.[0];
    const preview = document.getElementById('product-new-preview');
    const existing = document.getElementById('product-existing-image');
    const placeholder = document.getElementById('product-upload-placeholder');
    if (!file || !preview) return;
    preview.src = URL.createObjectURL(file);
    preview.classList.remove('hidden');
    existing?.classList.add('hidden');
    placeholder?.classList.add('hidden');
});
</script>
@endpush
@endonce
