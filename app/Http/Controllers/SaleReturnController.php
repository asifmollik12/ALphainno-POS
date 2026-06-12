<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = SaleReturn::query()
            ->where('user_id', $request->user()->id)
            ->with('customer')
            ->latest()
            ->paginate(20);

        return view('sale-returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $sales = Sale::where('user_id', $request->user()->id)->latest()->limit(50)->get();
        $products = Product::where('user_id', $request->user()->id)->orderBy('name')->get();

        return view('sale-returns.create', compact('sales', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sale_id' => ['nullable', 'exists:sales,id'],
            'return_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $userId = $request->user()->id;

        DB::transaction(function () use ($data, $userId) {
            $total = collect($data['items'])->sum(fn ($i) => round($i['quantity'] * $i['unit_price'], 2));
            $sale = isset($data['sale_id']) ? Sale::where('user_id', $userId)->find($data['sale_id']) : null;

            $return = SaleReturn::create([
                'user_id' => $userId,
                'sale_id' => $sale?->id,
                'customer_id' => $sale?->customer_id,
                'reference' => 'SRET-'.strtoupper(Str::random(8)),
                'total' => $total,
                'return_date' => $data['return_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::where('user_id', $userId)->findOrFail($item['product_id']);
                SaleReturnItem::create([
                    'sale_return_id' => $return->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => round($item['quantity'] * $item['unit_price'], 2),
                ]);
                $product->increment('stock', $item['quantity']);
            }

            if ($sale) {
                $sale->increment('returned_amount', $total);
            }
        });

        return redirect()->route('sale-returns.index')->with('success', 'Sale return recorded.');
    }
}
