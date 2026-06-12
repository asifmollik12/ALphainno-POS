<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $from = Carbon::parse($request->input('from', now()->subYear()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();

        $service = DashboardService::forUser($request->user()->id);
        $metrics = $service->metrics($from, $to);
        $monthly = $service->monthlyChart($from, $to);
        $transactions = $service->recentTransactions();
        $inventory = $service->inventorySummary();

        return view('dashboard.index', compact(
            'from', 'to', 'metrics', 'monthly', 'transactions', 'inventory'
        ));
    }
}
