<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Transaction;
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
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'customer_id' => ['nullable', 'exists:customers,id'],
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
                'customer_id' => $data['customer_id'] ?? null,
                'total' => round($total, 2),
                'paid_amount' => 0,
                'due_amount' => 0,
                'payment_status' => 'paid',
                'sale_date' => now()->toDateString(),
                'reference' => 'SALE-'.strtoupper(Str::random(8)),
            ]);

            $paid = (float) ($data['paid_amount'] ?? $total);
            $paid = min($paid, $total);
            $due = max(round($total - $paid, 2), 0);
            $sale->update([
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'due'),
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

            if ($paid > 0) {
                $cash = Account::where('user_id', $userId)->where('code', '1000')->first();
                $revenue = Account::where('user_id', $userId)->where('code', '4000')->first();
                if ($cash) {
                    Transaction::create([
                        'user_id' => $userId,
                        'account_id' => $cash->id,
                        'type' => 'credit',
                        'amount' => $paid,
                        'reference' => $sale->reference,
                        'description' => 'POS sale payment',
                        'transaction_date' => now()->toDateString(),
                        'related_type' => Sale::class,
                        'related_id' => $sale->id,
                    ]);
                    $cash->increment('current_balance', $paid);
                }
                if ($revenue) {
                    Transaction::create([
                        'user_id' => $userId,
                        'account_id' => $revenue->id,
                        'type' => 'credit',
                        'amount' => $paid,
                        'reference' => $sale->reference,
                        'description' => 'Sales revenue',
                        'transaction_date' => now()->toDateString(),
                        'related_type' => Sale::class,
                        'related_id' => $sale->id,
                    ]);
                    $revenue->increment('current_balance', $paid);
                }
            }

            return $sale;
        });

        return redirect()
            ->route('pos.index')
            ->with('success', "Sale completed — {$sale->reference} (৳".number_format($sale->total, 2).')');
    }
}
