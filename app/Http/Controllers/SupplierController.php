<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOwner;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    use AuthorizesOwner;

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $query = Supplier::query()->where('user_id', $userId);

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $suppliers = $query->latest('id')->paginate($perPage)->withQueryString();
        $currency = $request->user()->shopSetting?->currency ?? '৳';

        return view('suppliers.index', compact('suppliers', 'currency'));
    }

    public function create()
    {
        return redirect()->route('suppliers.index', ['create' => 1]);
    }

    public function store(Request $request)
    {
        $request->user()->suppliers()->create($this->validated($request));

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $this->authorizeOwner($supplier);
        $userId = auth()->id();

        $supplier->load([
            'purchases' => fn ($q) => $q->orderByDesc('purchase_date')->orderByDesc('id'),
        ]);

        $returns = PurchaseReturn::query()
            ->where('user_id', $userId)
            ->where('supplier_id', $supplier->id)
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->get();

        $purchaseIds = $supplier->purchases->pluck('id');
        $transactions = $purchaseIds->isEmpty()
            ? collect()
            : Transaction::query()
                ->where('user_id', $userId)
                ->where('related_type', Purchase::class)
                ->whereIn('related_id', $purchaseIds)
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->get();

        $stats = [
            'total' => (float) $supplier->purchases->sum('total'),
            'paid' => (float) $supplier->purchases->sum('paid_amount'),
            'return' => (float) $returns->sum('total'),
            'due' => (float) $supplier->purchases->sum('due_amount'),
        ];

        $currency = auth()->user()->shopSetting?->currency ?? '৳';

        return view('suppliers.show', compact('supplier', 'returns', 'transactions', 'stats', 'currency'));
    }

    public function edit(Supplier $supplier)
    {
        $this->authorizeOwner($supplier);

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->authorizeOwner($supplier);
        $supplier->update($this->validated($request, true));

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorizeOwner($supplier);
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted.');
    }

    public function print(Request $request)
    {
        $userId = $request->user()->id;
        $suppliers = Supplier::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();
        $setting = $request->user()->shopSetting;
        $currency = $setting?->currency ?? '৳';

        return view('suppliers.print', compact('suppliers', 'setting', 'currency'));
    }

    public function export(Request $request)
    {
        $userId = $request->user()->id;
        $suppliers = Supplier::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();

        $filename = 'suppliers-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($suppliers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Name', 'Phone', 'Email', 'Address', 'Balance Due']);
            foreach ($suppliers as $s) {
                fputcsv($out, [
                    $s->id,
                    $s->name,
                    $s->phone,
                    $s->email,
                    $s->address,
                    $s->balance_due,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function demoCsv()
    {
        $filename = 'suppliers-demo.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['name', 'phone', 'email', 'address']);
            fputcsv($out, ['Sample Supplier', '01700000000', 'supplier@example.com', 'Dhaka, Bangladesh']);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $userId = $request->user()->id;
        $path = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $count = 0;

        DB::transaction(function () use ($handle, $userId, &$count) {
            $header = null;
            while (($row = fgetcsv($handle)) !== false) {
                if ($header === null) {
                    $header = array_map('strtolower', array_map('trim', $row));
                    if (! in_array('name', $header, true)) {
                        $header = ['name', 'phone', 'email', 'address'];
                        // first row is data
                        $this->importSupplierRow($userId, $header, $row, $count);
                    }
                    continue;
                }
                $this->importSupplierRow($userId, $header, $row, $count);
            }
        });

        fclose($handle);

        return redirect()->route('suppliers.index')->with('success', "{$count} supplier(s) imported.");
    }

    private function importSupplierRow(int $userId, array $header, array $row, int &$count): void
    {
        $data = [];
        foreach ($header as $i => $key) {
            $data[$key] = trim($row[$i] ?? '');
        }
        $name = $data['name'] ?? '';
        $phone = $data['phone'] ?? '';
        if ($name === '' || $phone === '') {
            return;
        }
        Supplier::create([
            'user_id' => $userId,
            'name' => $name,
            'phone' => $phone,
            'email' => ($data['email'] ?? '') ?: null,
            'address' => ($data['address'] ?? '') ?: null,
        ]);
        $count++;
    }

    private function validated(Request $request, bool $requireAddress = false): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => [$requireAddress ? 'required' : 'nullable', 'string', 'max:500'],
        ]);
    }
}
