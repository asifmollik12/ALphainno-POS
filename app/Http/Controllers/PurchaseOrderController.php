<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = PurchaseOrder::query()
            ->where('user_id', $request->user()->id)
            ->with('supplier')
            ->latest()
            ->paginate(20);

        return view('purchase-orders.index', compact('orders'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::where('user_id', $request->user()->id)->orderBy('name')->get();
        $products = Product::where('user_id', $request->user()->id)->orderBy('name')->get();

        return view('purchase-orders.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $userId = $request->user()->id;
        $total = collect($data['items'])->sum(fn ($i) => round($i['quantity'] * $i['unit_cost'], 2));

        $order = PurchaseOrder::create([
            'user_id' => $userId,
            'supplier_id' => $data['supplier_id'] ?? null,
            'reference' => 'PO-'.strtoupper(Str::random(8)),
            'total' => $total,
            'status' => 'pending',
            'order_date' => $data['order_date'],
            'expected_date' => $data['expected_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            $product = Product::where('user_id', $userId)->findOrFail($item['product_id']);
            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'subtotal' => round($item['quantity'] * $item['unit_cost'], 2),
            ]);
        }

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order created.');
    }
}
