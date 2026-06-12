<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function inventory(Request $request)
    {
        $products = Product::where('user_id', $request->user()->id)->orderBy('name')->get();

        return view('reports.inventory', compact('products'));
    }

    public function purchase(Request $request)
    {
        $rows = Purchase::where('user_id', $request->user()->id)->with('supplier')->latest()->get();

        return view('reports.purchase', compact('rows'));
    }

    public function sale(Request $request)
    {
        $rows = Sale::where('user_id', $request->user()->id)->with('customer')->latest()->get();

        return view('reports.sale', compact('rows'));
    }

    public function supplier(Request $request)
    {
        $rows = Supplier::where('user_id', $request->user()->id)->withCount('purchases')->get();

        return view('reports.supplier', compact('rows'));
    }

    public function customer(Request $request)
    {
        $rows = Customer::where('user_id', $request->user()->id)->withCount('sales')->get();

        return view('reports.customer', compact('rows'));
    }

    public function payment(Request $request)
    {
        $from = Carbon::parse($request->input('from', now()->subMonth()));
        $to = Carbon::parse($request->input('to', now()));

        $transactions = Transaction::where('user_id', $request->user()->id)
            ->whereBetween('transaction_date', [$from, $to])
            ->with('account')
            ->latest('transaction_date')
            ->get();

        return view('reports.payment', compact('transactions', 'from', 'to'));
    }
}
