<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Purchase Invoices — {{ $setting->company_name ?? 'Alphainno POS' }}</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        body { font-family: system-ui, sans-serif; font-size: 10pt; color: #0c1222; margin: 0; }
        .toolbar { max-width: 1100px; margin: 12px auto; padding: 0 12px; }
        .btn { padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; text-decoration: none; color: #334155; margin-right: 8px; }
        .btn-primary { background: #0c1222; color: #fff; border-color: #0c1222; }
        .sheet { max-width: 1100px; margin: 12px auto 24px; padding: 16px; }
        @media print { .toolbar { display: none; } .sheet { margin: 0; padding: 0; } }
        h1 { font-size: 18pt; margin: 0 0 4px; }
        .meta { color: #64748b; font-size: 9pt; margin-bottom: 12px; }
        .stats { display: flex; gap: 16px; margin-bottom: 16px; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
        th { background: #0c1222; color: #fff; text-align: left; padding: 6px 5px; }
        td { padding: 5px; border-bottom: 1px solid #f1f5f9; }
        .r { text-align: right; }
    </style>
</head>
<body>
@php $fmt = fn ($n) => $currency . number_format($n, 2); @endphp

<div class="toolbar">
    <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
    <a href="{{ route('purchases.index', request()->only(['from','to'])) }}" class="btn">Back</a>
</div>

<div class="sheet">
    <h1>Purchase Invoices</h1>
    <div class="meta">{{ $setting->company_name ?? 'Alphainno POS' }} · {{ $from }} to {{ $to }} · Printed {{ now()->format('d/m/Y H:i') }}</div>
    <div class="stats">
        <span>Total: {{ $fmt($stats['total']) }}</span>
        <span>Paid: {{ $fmt($stats['paid']) }}</span>
        <span>Due: {{ $fmt($stats['due']) }}</span>
        <span>Returns: {{ $fmt($stats['returns']) }}</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Supplier</th>
                <th>Date</th>
                <th class="r">Total</th>
                <th class="r">Paid</th>
                <th class="r">Due</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchases as $p)
            <tr>
                <td>{{ $p->reference }}</td>
                <td>{{ $p->supplier->name ?? '—' }}</td>
                <td>{{ $p->purchase_date->format('M d, Y') }}</td>
                <td class="r">{{ $fmt($p->total) }}</td>
                <td class="r">{{ $fmt($p->paid_amount) }}</td>
                <td class="r">{{ $fmt($p->due_amount) }}</td>
                <td>{{ strtoupper($p->payment_status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
