<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $sales = Sale::query()
            ->where('user_id', $request->user()->id)
            ->with(['items', 'customer'])
            ->latest()
            ->paginate(20);

        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale)
    {
        abort_unless($sale->user_id === auth()->id(), 403);
        $sale->load(['items', 'customer']);

        return view('sales.show', compact('sale'));
    }

    public function invoice(Sale $sale)
    {
        abort_unless($sale->user_id === auth()->id(), 403);
        $sale->load(['items', 'customer']);
        $setting = auth()->user()->shopSetting;

        return view('sales.invoice', compact('sale', 'setting'));
    }
}
