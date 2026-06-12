<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
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
        $userId = $request->user()->id;
        $setting = $request->user()->shopSetting;

        $products = Product::query()
            ->where('user_id', $userId)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        $customers = Customer::where('user_id', $userId)->orderBy('name')->get();
        $categories = Product::where('user_id', $userId)->whereNotNull('category')->distinct()->pluck('category')->filter()->sort()->values();
        $brands = Product::where('user_id', $userId)->whereNotNull('brand')->distinct()->pluck('brand')->filter()->sort()->values();

        $productCatalog = $products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'price' => (float) $p->price,
            'stock' => $p->stock,
            'unit' => $p->unit ?? 'Pcs',
            'category' => $p->category,
            'brand' => $p->brand,
            'barcode' => $p->barcode ?? $p->sku,
        ])->values();

        return view('pos.index', compact('products', 'customers', 'categories', 'brands', 'setting', 'productCatalog'));
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'warehouse' => ['nullable', 'string', 'max:255'],
            'delivery_status' => ['nullable', 'string', 'max:30'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'order_discount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $userId = $request->user()->id;
        $taxRate = (float) ($request->user()->shopSetting?->default_tax_rate ?? 0);

        $sale = DB::transaction(function () use ($data, $userId, $taxRate) {
            $productIds = collect($data['items'])->pluck('product_id')->unique()->all();
            $products = Product::query()
                ->where('user_id', $userId)
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $totalTax = 0;
            $lineDiscountTotal = 0;
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

                $lineSub = round($product->price * $qty, 2);
                $discount = round((float) ($item['discount'] ?? 0), 2);
                $taxable = max($lineSub - $discount, 0);
                $lineTax = round($taxable * ($taxRate / 100), 2);
                $lineTotal = round($taxable + $lineTax, 2);

                $subtotal += $lineSub;
                $lineDiscountTotal += $discount;
                $totalTax += $lineTax;

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'discount' => $discount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'subtotal' => $lineTotal,
                ];
            }

            $orderDiscount = round((float) ($data['order_discount'] ?? 0), 2);
            $grandTotal = max(round($subtotal - $lineDiscountTotal - $orderDiscount + $totalTax, 2), 0);

            $nextNum = Sale::where('user_id', $userId)->count() + 1;
            $reference = 'SO#'.str_pad((string) $nextNum, 8, '0', STR_PAD_LEFT);

            $paid = (float) ($data['paid_amount'] ?? $grandTotal);
            $paid = min($paid, $grandTotal);
            $due = max(round($grandTotal - $paid, 2), 0);

            $sale = Sale::create([
                'user_id' => $userId,
                'customer_id' => $data['customer_id'] ?? null,
                'reference' => $reference,
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => $lineDiscountTotal + $orderDiscount,
                'total' => $grandTotal,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'due'),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'warehouse' => $data['warehouse'] ?? null,
                'delivery_status' => $data['delivery_status'] ?? 'delivered',
                'payment_reference' => $data['payment_reference'] ?? null,
                'sale_date' => now()->toDateString(),
            ]);

            foreach ($lineItems as $line) {
                $product = $line['product'];
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit' => $product->unit ?? 'Pcs',
                    'quantity' => $line['quantity'],
                    'unit_price' => $product->price,
                    'discount' => $line['discount'],
                    'tax_rate' => $line['tax_rate'],
                    'tax_amount' => $line['tax_amount'],
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
                        'description' => 'POS sale ('.($sale->payment_method ?? 'cash').')',
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

        return redirect()->route('sales.invoice', $sale);
    }
}
