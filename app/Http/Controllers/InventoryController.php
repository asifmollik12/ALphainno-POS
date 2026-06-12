<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function shortage(Request $request)
    {
        $userId = $request->user()->id;
        $query = Product::query()
            ->where('user_id', $userId)
            ->whereColumn('stock', '<=', 'min_stock');

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('stock')->paginate(20)->withQueryString();
        $currency = $request->user()->shopSetting?->currency ?? '৳';
        $totalShort = Product::where('user_id', $userId)->whereColumn('stock', '<=', 'min_stock')->count();

        return view('inventory.shortage', compact('products', 'currency', 'totalShort'));
    }

    public function shortagePrint(Request $request)
    {
        $userId = $request->user()->id;
        $products = Product::query()
            ->where('user_id', $userId)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->get();
        $setting = $request->user()->shopSetting;
        $currency = $setting?->currency ?? '৳';

        return view('inventory.shortage-print', compact('products', 'setting', 'currency'));
    }

    public function shortageExport(Request $request)
    {
        $userId = $request->user()->id;
        $products = Product::query()
            ->where('user_id', $userId)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->get();
        $filename = 'shortage-products-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($products) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Name', 'SKU', 'Quantity', 'Purchase Price', 'Sale Price', 'Reorder Qty']);
            foreach ($products as $p) {
                fputcsv($out, [$p->id, $p->name, $p->sku, $p->stock, $p->cost_price, $p->price, $p->min_stock]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
