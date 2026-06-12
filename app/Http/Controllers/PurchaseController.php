<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOwner;
use App\Models\Account;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
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
        $userId = $request->user()->id;
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        $query = Purchase::query()
            ->where('user_id', $userId)
            ->with('supplier')
            ->whereDate('purchase_date', '>=', $from)
            ->whereDate('purchase_date', '<=', $to);

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        $purchases = $query->latest('purchase_date')->latest('id')->paginate(20)->withQueryString();
        $stats = $this->purchaseStats($userId, $from, $to);
        $currency = $request->user()->shopSetting?->currency ?? '৳';

        return view('purchases.index', compact('purchases', 'stats', 'currency', 'from', 'to'));
    }

    public function print(Request $request)
    {
        $userId = $request->user()->id;
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        $purchases = Purchase::query()
            ->where('user_id', $userId)
            ->with('supplier')
            ->whereDate('purchase_date', '>=', $from)
            ->whereDate('purchase_date', '<=', $to)
            ->orderByDesc('purchase_date')
            ->get();

        $stats = $this->purchaseStats($userId, $from, $to);
        $setting = $request->user()->shopSetting;
        $currency = $setting?->currency ?? '৳';

        return view('purchases.print', compact('purchases', 'stats', 'setting', 'currency', 'from', 'to'));
    }

    public function export(Request $request)
    {
        $userId = $request->user()->id;
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        $purchases = Purchase::query()
            ->where('user_id', $userId)
            ->with('supplier')
            ->whereDate('purchase_date', '>=', $from)
            ->whereDate('purchase_date', '<=', $to)
            ->orderByDesc('purchase_date')
            ->get();

        $filename = 'purchase-invoices-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($purchases) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reference', 'Supplier', 'Date', 'Total', 'Paid', 'Due', 'Tax', 'Status']);
            foreach ($purchases as $p) {
                fputcsv($out, [
                    $p->reference,
                    $p->supplier->name ?? '',
                    $p->purchase_date->format('Y-m-d'),
                    $p->total,
                    $p->paid_amount,
                    $p->due_amount,
                    0,
                    $p->payment_status,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::where('user_id', $request->user()->id)->orderBy('name')->get();
        $products = Product::where('user_id', $request->user()->id)->orderBy('name')->get();
        $productCatalog = $products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'cost_price' => (float) $p->cost_price,
            'price' => (float) $p->price,
        ])->values();
        $currency = $request->user()->shopSetting?->currency ?? '৳';

        return view('purchases.create', compact('suppliers', 'products', 'productCatalog', 'currency'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $userId = $request->user()->id;

        DB::transaction(function () use ($data, $userId) {
            $total = 0;
            foreach ($data['items'] as $item) {
                $line = round($item['quantity'] * $item['unit_cost'], 2);
                $taxRate = (float) ($item['tax_rate'] ?? 0);
                $total += round($line + ($line * $taxRate / 100), 2);
            }
            $paid = min((float) ($data['paid_amount'] ?? 0), $total);
            $due = max(round($total - $paid, 2), 0);
            $status = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'due');

            $purchase = Purchase::create([
                'user_id' => $userId,
                'supplier_id' => $data['supplier_id'],
                'reference' => 'P_'.strtoupper(Str::random(12)),
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
                $this->recordPayment($userId, $purchase, $paid, $data['purchase_date'], $data['payment_method'] ?? 'cash');
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase invoice created.');
    }

    public function show(Purchase $purchase)
    {
        $this->authorizeOwner($purchase);
        $purchase->load(['items.product', 'supplier', 'transactions.account']);
        $currency = auth()->user()->shopSetting?->currency ?? '৳';

        return view('purchases.show', compact('purchase', 'currency'));
    }

    public function pay(Request $request, Purchase $purchase)
    {
        $this->authorizeOwner($purchase);

        $data = $request->validate([
            'paid_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:30'],
        ]);

        $amount = min((float) $data['paid_amount'], (float) $purchase->due_amount);
        if ($amount <= 0) {
            return back()->withErrors(['paid_amount' => 'Nothing due on this invoice.']);
        }

        DB::transaction(function () use ($purchase, $amount, $data) {
            $newPaid = round($purchase->paid_amount + $amount, 2);
            $newDue = max(round($purchase->total - $newPaid, 2), 0);
            $status = $newDue <= 0 ? 'paid' : 'partial';

            $purchase->update([
                'paid_amount' => $newPaid,
                'due_amount' => $newDue,
                'payment_status' => $status,
            ]);

            $this->recordPayment($purchase->user_id, $purchase, $amount, $data['payment_date'], $data['payment_method'] ?? 'cash');
        });

        return back()->with('success', 'Payment recorded.');
    }

    /** @return array{count:int,total:float,paid:float,due:float,returns:float} */
    private function purchaseStats(int $userId, string $from, string $to): array
    {
        $base = Purchase::query()
            ->where('user_id', $userId)
            ->whereDate('purchase_date', '>=', $from)
            ->whereDate('purchase_date', '<=', $to);

        $returns = (float) PurchaseReturn::query()
            ->where('user_id', $userId)
            ->whereDate('return_date', '>=', $from)
            ->whereDate('return_date', '<=', $to)
            ->sum('total');

        return [
            'count' => (clone $base)->count(),
            'total' => (float) (clone $base)->sum('total'),
            'paid' => (float) (clone $base)->sum('paid_amount'),
            'due' => (float) (clone $base)->sum('due_amount'),
            'returns' => $returns,
        ];
    }

    private function recordPayment(int $userId, Purchase $purchase, float $amount, string $date, string $method): void
    {
        $cash = Account::where('user_id', $userId)->where('code', '1000')->first();
        if (! $cash) {
            return;
        }

        Transaction::create([
            'user_id' => $userId,
            'account_id' => $cash->id,
            'type' => 'debit',
            'amount' => $amount,
            'reference' => $purchase->reference,
            'description' => 'Purchase payment ('.$method.')',
            'transaction_date' => $date,
            'related_type' => Purchase::class,
            'related_id' => $purchase->id,
        ]);
        $cash->decrement('current_balance', $amount);
    }
}
