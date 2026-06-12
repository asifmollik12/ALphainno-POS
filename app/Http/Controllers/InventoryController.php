<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function shortage(Request $request)
    {
        $products = Product::query()
            ->where('user_id', $request->user()->id)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->paginate(20);

        return view('inventory.shortage', compact('products'));
    }
}
