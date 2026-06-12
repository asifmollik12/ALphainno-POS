<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->where('user_id', $request->user()->id)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        return view('pos.index', compact('products'));
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $userId = $request->user()->id;

        $sale = DB::transaction(function () use ($data, $userId) {
            $productIds = collect($data['items'])->pluck('product_id')->unique()->all();

            $products = Product::query()
                ->where('user_id', $userId)
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;
            $lineItems = [];

            foreach ($data['items'] as $item) {
                $product = $products->get($item['product_id']);

                if (! $product) {
                    abort(422, 'Product not found.');
                }

                $qty = (int) $item['quantity'];

                if ($product->stock < $qty) {
                    abort(422, "Not enough stock for {$product->name}.");
                }

                $subtotal = round($product->price * $qty, 2);
                $total += $subtotal;

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            $sale = Sale::create([
                'user_id' => $userId,
                'total' => round($total, 2),
                'reference' => 'SALE-'.strtoupper(Str::random(8)),
            ]);

            foreach ($lineItems as $line) {
                /** @var Product $product */
                $product = $line['product'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $line['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $line['subtotal'],
                ]);

                $product->decrement('stock', $line['quantity']);
            }

            return $sale;
        });

        return redirect()
            ->route('pos.index')
            ->with('success', "Sale completed — {$sale->reference} (৳".number_format($sale->total, 2).')');
    }
}
