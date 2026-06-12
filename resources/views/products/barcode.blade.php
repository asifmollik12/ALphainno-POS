<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barcode — {{ $product->name }}</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; background: #f1f5f9; color: #0c1222; }
        .toolbar { padding: 12px 16px; display: flex; gap: 8px; }
        .btn { padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-size: 13px; }
        .btn-dark { background: #0c1222; color: #fff; border-color: #0c1222; }
        .label { width: 280px; margin: 24px auto; padding: 20px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; }
        .name { font-size: 14px; font-weight: 600; margin-bottom: 4px; }
        .meta { font-size: 11px; color: #64748b; margin-bottom: 12px; }
        .code { font-family: ui-monospace, monospace; font-size: 13px; letter-spacing: 0.08em; margin-top: 8px; }
        @media print { .toolbar { display: none; } body { background: #fff; } .label { border: none; margin: 0; } }
    </style>
</head>
<body>
@php $barcodeValue = $product->barcode ?: ($product->sku ?: (string) $product->id); @endphp

<div class="toolbar">
    <button type="button" class="btn btn-dark" onclick="window.print()">Print</button>
    <button type="button" class="btn" onclick="window.close()">Close</button>
</div>

<div class="label">
    <div class="name">{{ $product->name }}</div>
    <div class="meta">SKU: {{ $product->sku ?: '—' }} · {{ $currency }}{{ number_format($product->price, 2) }}</div>
    <svg id="barcode"></svg>
    <div class="code">{{ $barcodeValue }}</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
JsBarcode('#barcode', @json($barcodeValue), {
    format: 'CODE128',
    lineColor: '#0c1222',
    width: 2,
    height: 70,
    displayValue: false,
    margin: 0,
});
</script>
</body>
</html>
