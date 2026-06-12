<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(private int $userId) {}

    public static function forUser(int $userId): self
    {
        return new self($userId);
    }

    public function metrics(Carbon $from, Carbon $to): array
    {
        $sales = Sale::query()
            ->where('user_id', $this->userId)
            ->whereBetween('sale_date', [$from, $to]);

        $purchases = Purchase::query()
            ->where('user_id', $this->userId)
            ->whereBetween('purchase_date', [$from, $to]);

        $saleReturns = SaleReturn::query()
            ->where('user_id', $this->userId)
            ->whereBetween('return_date', [$from, $to]);

        return [
            'total_sale' => (float) (clone $sales)->sum('total'),
            'total_sale_due' => (float) (clone $sales)->sum('due_amount'),
            'total_purchase' => (float) (clone $purchases)->sum('total'),
            'total_purchase_due' => (float) (clone $purchases)->sum('due_amount'),
            'sale_paid' => (float) (clone $sales)->sum('paid_amount'),
            'sale_return' => (float) (clone $saleReturns)->sum('total'),
            'purchase_paid' => (float) (clone $purchases)->sum('paid_amount'),
            'purchase_return' => (float) (clone $purchases)->sum('returned_amount'),
        ];
    }

    public function monthlyChart(Carbon $from, Carbon $to): array
    {
        $months = [];
        $cursor = $from->copy()->startOfMonth();
        while ($cursor <= $to) {
            $key = $cursor->format('Y-m');
            $months[$key] = ['label' => $cursor->format('M Y'), 'sales' => 0, 'purchases' => 0];
            $cursor->addMonth();
        }

        $saleRows = Sale::query()
            ->where('user_id', $this->userId)
            ->whereBetween('sale_date', [$from, $to])
            ->selectRaw("DATE_FORMAT(sale_date, '%Y-%m') as month, SUM(total) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $purchaseRows = Purchase::query()
            ->where('user_id', $this->userId)
            ->whereBetween('purchase_date', [$from, $to])
            ->selectRaw("DATE_FORMAT(purchase_date, '%Y-%m') as month, SUM(total) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        foreach ($months as $key => &$row) {
            $row['sales'] = (float) ($saleRows[$key] ?? 0);
            $row['purchases'] = (float) ($purchaseRows[$key] ?? 0);
        }

        return array_values($months);
    }

    public function sparkline(Carbon $from, Carbon $to, string $type): array
    {
        $table = $type === 'purchase' ? 'purchases' : 'sales';
        $dateCol = $type === 'purchase' ? 'purchase_date' : 'sale_date';
        $valueCol = $type === 'purchase' ? 'total' : 'total';

        return DB::table($table)
            ->where('user_id', $this->userId)
            ->whereBetween($dateCol, [$from, $to])
            ->selectRaw("DATE($dateCol) as day, SUM($valueCol) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total')
            ->map(fn ($v) => (float) $v)
            ->values()
            ->all();
    }

    public function recentTransactions(int $limit = 8): Collection
    {
        return Transaction::query()
            ->where('user_id', $this->userId)
            ->with('account')
            ->latest('transaction_date')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function inventorySummary(): array
    {
        $products = Product::query()->where('user_id', $this->userId);

        return [
            'total_products' => (clone $products)->count(),
            'total_stock_value' => (float) (clone $products)->selectRaw('SUM(stock * price) as v')->value('v'),
            'shortage_count' => (clone $products)->whereColumn('stock', '<=', 'min_stock')->count(),
        ];
    }
}
