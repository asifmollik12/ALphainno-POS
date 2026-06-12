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
            ->with('items')
            ->latest()
            ->paginate(20);

        return view('sales.index', compact('sales'));
    }
}
