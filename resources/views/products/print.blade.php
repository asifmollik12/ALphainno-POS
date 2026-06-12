<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product List — {{ $setting->company_name ?? 'Alphainno POS' }}</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; font-size: 10pt; color: #0c1222; }
        .toolbar { max-width: 1100px; margin: 12px auto; padding: 0 12px; display: flex; gap: 8px; }
        .btn { padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-size: 13px; text-decoration: none; color: #334155; }
        .btn-primary { background: #1e3a8a; color: #fff; border-color: #1e3a8a; }
        .sheet { max-width: 1100px; margin: 12px auto 24px; padding: 16px; background: #fff; }
        @media print { .toolbar { display: none; } .sheet { margin: 0; padding: 0; max-width: none; } }
        h1 { font-size: 18pt; margin-bottom: 4px; }
        .meta { color: #64748b; font-size: 9pt; margin-bottom: 12px; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 14px; }
        .stat { border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; }
        .stat label { display: block; font-size: 8pt; color: #64748b; text-transform: uppercase; }
        .stat strong { font-size: 12pt; }
        table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
        th { background: #0c1222; color: #fff; text-align: left; padding: 6px 5px; font-size: 7.5pt; text-transform: uppercase; }
        td { padding: 5px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        tr:nth-child(even) td { background: #f8fafc; }
        .r { text-align: right; }
        .short { color: #dc2626; font-weight: 700; }
    </style>
</head>
<body>
@php
    $fmt = fn ($n) => $currency . number_format($n, 2);
    $fmtCompact = function ($n) use ($currency) {
        if ($n >= 1_000_000) return $currency . number_format($n / 1_000_000, 1) . 'M';
        if ($n >= 1_000) return $currency . number_format($n / 1_000, 1) . 'K';
        return $currency . number_format($n, 0);
    };
@endphp

<div class="toolbar">
    <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
    <a href="{{ route('products.index') }}" class="btn">Back to Products</a>
</div>

<div class="sheet">
    <h1>Product List</h1>
    <div class="meta">
        {{ $setting->company_name ?? 'Alphainno POS' }} · Printed {{ now()->format('d/m/Y H:i') }} · {{ $products->count() }} items
    </div>

    <div class="stats">
        <div class="stat"><label>Unique Product</label><strong>{{ number_format($stats['unique']) }}</strong></div>
        <div class="stat"><label>Inventory Sale Value</label><strong>{{ $fmtCompact($stats['sale_value']) }}</strong></div>
        <div class="stat"><label>Inventory Purchase Value</label><strong>{{ $fmtCompact($stats['purchase_value']) }}</strong></div>
        <div class="stat"><label>Short Product</label><strong>{{ number_format($stats['short_count']) }}</strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Brand</th>
                <th>Category</th>
                <th>SKU</th>
                <th class="r">Purchase</th>
                <th class="r">Sale</th>
                <th class="r">Qty</th>
                <th>UOM</th>
                <th class="r">Reorder</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td><strong>{{ $product->name }}</strong></td>
                <td>{{ $product->brand ?: '—' }}</td>
                <td>{{ $product->category ?: '—' }}</td>
                <td>{{ $product->sku ?: '—' }}</td>
                <td class="r">{{ $fmt($product->cost_price) }}</td>
                <td class="r">{{ $fmt($product->price) }}</td>
                <td class="r {{ $product->isShortage() ? 'short' : '' }}">{{ $product->stock }}</td>
                <td>{{ $product->unit ?? 'Pcs' }}</td>
                <td class="r">{{ $product->min_stock }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
