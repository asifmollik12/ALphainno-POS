<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOwner;
use App\Models\Account;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    use AuthorizesOwner;

    public function index(Request $request)
    {
        $purchases = Purchase::query()
            ->where('user_id', $request->user()->id)
            ->with('supplier')
            ->latest()
            ->paginate(20);

        return view('purchases.index', compact('purchases'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::where('user_id', $request->user()->id)->orderBy('name')->get();
        $products = Product::where('user_id', $request->user()->id)->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $userId = $request->user()->id;

        DB::transaction(function () use ($data, $userId, $request) {
            $total = 0;
            foreach ($data['items'] as $item) {
                $total += round($item['quantity'] * $item['unit_cost'], 2);
            }
            $paid = (float) ($data['paid_amount'] ?? 0);
            $due = max(round($total - $paid, 2), 0);
            $status = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'due');

            $purchase = Purchase::create([
                'user_id' => $userId,
                'supplier_id' => $data['supplier_id'] ?? null,
                'reference' => 'PUR-'.strtoupper(Str::random(8)),
                'total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $status,
                'purchase_date' => $data['purchase_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::where('user_id', $userId)->findOrFail($item['product_id']);
                $subtotal = round($item['quantity'] * $item['unit_cost'], 2);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);

                $product->increment('stock', $item['quantity']);
                $product->update(['cost_price' => $item['unit_cost']]);
            }

            if ($paid > 0) {
                $cash = Account::where('user_id', $userId)->where('code', '1000')->first();
                if ($cash) {
                    Transaction::create([
                        'user_id' => $userId,
                        'account_id' => $cash->id,
                        'type' => 'debit',
                        'amount' => $paid,
                        'reference' => $purchase->reference,
                        'description' => 'Purchase payment',
                        'transaction_date' => $data['purchase_date'],
                        'related_type' => Purchase::class,
                        'related_id' => $purchase->id,
                    ]);
                    $cash->decrement('current_balance', $paid);
                }
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase invoice created.');
    }

    public function show(Purchase $purchase)
    {
        $this->authorizeOwner($purchase);
        $purchase->load(['items', 'supplier']);

        return view('purchases.show', compact('purchase'));
    }
}
