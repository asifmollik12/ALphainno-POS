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
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseReturnController extends Controller
{
    use AuthorizesOwner;

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        $query = PurchaseReturn::query()
            ->where('user_id', $userId)
            ->with(['supplier', 'purchase'])
            ->whereDate('return_date', '>=', $from)
            ->whereDate('return_date', '<=', $to);

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('purchase', fn ($p) => $p->where('reference', 'like', "%{$search}%"));
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $returns = $query->latest('return_date')->latest('id')->paginate($perPage)->withQueryString();

        return view('purchase-returns.index', compact('returns', 'from', 'to'));
    }

    public function show(Request $request, PurchaseReturn $purchaseReturn)
    {
        $this->authorizeOwner($purchaseReturn);
        $purchaseReturn->load(['supplier', 'purchase', 'items']);
        $currency = $request->user()->shopSetting?->currency ?? '৳';

        return view('purchase-returns.show', [
            'return' => $purchaseReturn,
            'currency' => $currency,
        ]);
    }

    public function print(Request $request)
    {
        $userId = $request->user()->id;
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        $returns = PurchaseReturn::query()
            ->where('user_id', $userId)
            ->with(['supplier', 'purchase'])
            ->whereDate('return_date', '>=', $from)
            ->whereDate('return_date', '<=', $to)
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->get();

        $setting = $request->user()->shopSetting;
        $currency = $setting?->currency ?? '৳';

        return view('purchase-returns.print', compact('returns', 'setting', 'currency', 'from', 'to'));
    }

    public function export(Request $request): StreamedResponse
    {
        $userId = $request->user()->id;
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        $returns = PurchaseReturn::query()
            ->where('user_id', $userId)
            ->with(['supplier', 'purchase'])
            ->whereDate('return_date', '>=', $from)
            ->whereDate('return_date', '<=', $to)
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->get();

        $filename = 'purchase-returns-'.$from.'-'.$to.'.csv';

        return response()->streamDownload(function () use ($returns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Date', 'Purchase Invoice ID', 'Supplier', 'Total', 'Notes']);
            foreach ($returns as $r) {
                fputcsv($out, [
                    $r->reference,
                    $r->return_date->format('Y-m-d'),
                    $r->purchase?->reference ?? '',
                    $r->supplier?->name ?? '',
                    $r->total,
                    $r->notes ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function create(Request $request)
    {
        $userId = $request->user()->id;
        $suppliers = Supplier::where('user_id', $userId)->orderBy('name')->get();
        $products = Product::where('user_id', $userId)->orderBy('name')->get();
        $purchases = Purchase::where('user_id', $userId)->latest('purchase_date')->limit(50)->get(['id', 'reference', 'supplier_id']);

        return view('purchase-returns.create', compact('suppliers', 'products', 'purchases'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_id' => ['nullable', 'exists:purchases,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'return_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $userId = $request->user()->id;

        if ($data['purchase_id']) {
            $purchase = Purchase::where('user_id', $userId)->findOrFail($data['purchase_id']);
            $data['supplier_id'] = $data['supplier_id'] ?? $purchase->supplier_id;
        }

        DB::transaction(function () use ($data, $userId) {
            $total = 0;
            foreach ($data['items'] as $item) {
                $total += round($item['quantity'] * $item['unit_cost'], 2);
            }

            $return = PurchaseReturn::create([
                'user_id' => $userId,
                'purchase_id' => $data['purchase_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'reference' => 'PR_'.strtoupper(Str::random(12)),
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
