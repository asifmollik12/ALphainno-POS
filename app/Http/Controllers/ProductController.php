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
