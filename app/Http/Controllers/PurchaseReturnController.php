<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOwner;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseReturnController extends Controller
{
    use AuthorizesOwner;

    public function index(Request $request)
    {
        $returns = PurchaseReturn::query()
            ->where('user_id', $request->user()->id)
            ->with('supplier')
            ->latest()
            ->paginate(20);

        return view('purchase-returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::where('user_id', $request->user()->id)->orderBy('name')->get();
        $products = Product::where('user_id', $request->user()->id)->orderBy('name')->get();

        return view('purchase-returns.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'return_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $userId = $request->user()->id;

        DB::transaction(function () use ($data, $userId) {
            $total = 0;
            foreach ($data['items'] as $item) {
                $total += round($item['quantity'] * $item['unit_cost'], 2);
            }

            $return = PurchaseReturn::create([
                'user_id' => $userId,
                'supplier_id' => $data['supplier_id'] ?? null,
                'reference' => 'PRET-'.strtoupper(Str::random(8)),
                'total' => $total,
                'return_date' => $data['return_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::where('user_id', $userId)->findOrFail($item['product_id']);
                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => round($item['quantity'] * $item['unit_cost'], 2),
                ]);
                $product->decrement('stock', min($item['quantity'], $product->stock));
            }
        });

        return redirect()->route('purchase-returns.index')->with('success', 'Purchase return recorded.');
    }
}
