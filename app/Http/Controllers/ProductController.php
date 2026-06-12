<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Uploads;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $query = Product::query()->where('user_id', $userId);

        $stats = $this->productStats($userId);

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate(20)->withQueryString();
        $currency = $request->user()->shopSetting?->currency ?? '৳';

        return view('products.index', compact('products', 'stats', 'currency'));
    }

    public function print(Request $request)
    {
        $userId = $request->user()->id;
        $products = Product::query()->where('user_id', $userId)->orderBy('name')->get();
        $stats = $this->productStats($userId);
        $setting = $request->user()->shopSetting;
        $currency = $setting?->currency ?? '৳';

        return view('products.print', compact('products', 'stats', 'setting', 'currency'));
    }

    public function export(Request $request)
    {
        $userId = $request->user()->id;
        $products = Product::query()->where('user_id', $userId)->orderBy('name')->get();
        $filename = 'products-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($products) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Name', 'Brand', 'Category', 'SKU', 'Purchase Price', 'Sale Price', 'Quantity', 'UOM', 'Reorder Qty']);
            foreach ($products as $p) {
                fputcsv($out, [
                    $p->id,
                    $p->name,
                    $p->brand,
                    $p->category,
                    $p->sku,
                    $p->cost_price,
                    $p->price,
                    $p->stock,
                    $p->unit ?? 'Pcs',
                    $p->min_stock,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /** @return array{unique:int,sale_value:float,purchase_value:float,short_count:int} */
    private function productStats(int $userId): array
    {
        $base = Product::query()->where('user_id', $userId);

        return [
            'unique' => (clone $base)->count(),
            'sale_value' => (float) ((clone $base)->selectRaw('COALESCE(SUM(stock * price), 0) as v')->value('v') ?? 0),
            'purchase_value' => (float) ((clone $base)->selectRaw('COALESCE(SUM(stock * cost_price), 0) as v')->value('v') ?? 0),
            'short_count' => (clone $base)->whereColumn('stock', '<=', 'min_stock')->count(),
        ];
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        unset($data['image']);
        $product = $request->user()->products()->create($data);

        if ($request->hasFile('image')) {
            $product->update([
                'image_path' => Uploads::storeImage($request->file('image'), 'products/'.$request->user()->id),
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product added.');
    }

    public function show(Product $product, Request $request)
    {
        $this->authorizeProduct($product);
        $currency = $request->user()->shopSetting?->currency ?? '৳';

        return view('products.show', compact('product', 'currency'));
    }

    public function printBarcode(Request $request)
    {
        $userId = $request->user()->id;
        $settings = $this->barcodeSettings($request);
        $selectedIds = $this->barcodeProductIds($request);

        $selectedProducts = $selectedIds
            ? Product::where('user_id', $userId)->whereIn('id', $selectedIds)->orderBy('name')->get()
            : collect();

        return view('products.print-barcode', compact('settings', 'selectedProducts', 'selectedIds'));
    }

    public function printBarcodePreview(Request $request)
    {
        $userId = $request->user()->id;
        $settings = $this->barcodeSettings($request);
        $selectedIds = $this->barcodeProductIds($request);

        $products = $selectedIds
            ? Product::where('user_id', $userId)->whereIn('id', $selectedIds)->orderBy('name')->get()
            : Product::where('user_id', $userId)->orderBy('name')->get();

        if ($products->isEmpty()) {
            abort(404, 'No products selected for barcode printing.');
        }

        $slots = max($settings['columns'] * $settings['rows'], 1);
        $labels = collect();
        for ($i = 0; $i < $slots; $i++) {
            $labels->push($products[$i % $products->count()]);
        }

        $currency = $request->user()->shopSetting?->currency ?? '৳';
        $autoPrint = $request->boolean('print');

        return view('products.print-barcode-sheet', compact('labels', 'settings', 'currency', 'autoPrint'));
    }

    public function barcode(Product $product, Request $request)
    {
        $this->authorizeProduct($product);

        return redirect()->route('products.print-barcode', [
            'product' => $product->id,
            ...$this->barcodeSettings($request),
        ]);
    }

    /** @return array{page_size:string,columns:int,rows:int,height:int,width:float,font_size:int} */
    private function barcodeSettings(Request $request): array
    {
        return [
            'page_size' => $request->input('page_size', 'A4'),
            'columns' => max((int) $request->input('columns', 3), 1),
            'rows' => max((int) $request->input('rows', 10), 1),
            'height' => max((int) $request->input('height', 60), 20),
            'width' => max((float) $request->input('width', 1.5), 0.5),
            'font_size' => max((int) $request->input('font_size', 15), 8),
        ];
    }

    /** @return list<int> */
    private function barcodeProductIds(Request $request): array
    {
        $ids = $request->input('products', []);
        if (! is_array($ids)) {
            $ids = [$ids];
        }
        if ($request->filled('product')) {
            $ids[] = $request->input('product');
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    public function edit(Product $product)
    {
        $this->authorizeProduct($product);

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        $data = $this->validated($request);
        unset($data['image']);
        $product->update($data);

        if ($request->hasFile('image')) {
            $product->update([
                'image_path' => Uploads::storeImage(
                    $request->file('image'),
                    'products/'.$request->user()->id,
                    $product->image_path
                ),
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $this->authorizeProduct($product);

        if ($product->image_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:30'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ], [], ['image' => 'product photo']);
    }

    private function authorizeProduct(Product $product): void
    {
        abort_unless($product->user_id === auth()->id(), 403);
    }
}
