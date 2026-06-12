<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barcode Preview</title>
    <style>
        @page { size: {{ $settings['page_size'] }}; margin: 8mm; }
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; background: #e2e8f0; color: #0c1222; }
        .toolbar { padding: 12px 16px; display: flex; gap: 8px; }
        .btn { padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-size: 13px; text-decoration: none; color: #334155; }
        .btn-dark { background: #0c1222; color: #fff; border-color: #0c1222; }
        .sheet {
            width: {{ $settings['page_size'] === 'Letter' ? '215.9mm' : '210mm' }};
            min-height: {{ $settings['page_size'] === 'Letter' ? '279.4mm' : '297mm' }};
            margin: 12px auto 24px;
            padding: 6mm;
            background: #fff;
            display: grid;
            grid-template-columns: repeat({{ $settings['columns'] }}, 1fr);
            grid-template-rows: repeat({{ $settings['rows'] }}, 1fr);
            gap: 2mm;
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; width: auto; min-height: auto; padding: 0; }
        }
        .label {
            border: 1px dashed #e2e8f0;
            padding: 2mm;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .name {
            font-size: {{ $settings['font_size'] }}px;
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 1mm;
            word-break: break-word;
        }
        .price { font-size: {{ max($settings['font_size'] - 3, 8) }}px; color: #64748b; margin-bottom: 1mm; }
        .code { font-family: ui-monospace, monospace; font-size: {{ max($settings['font_size'] - 4, 8) }}px; letter-spacing: 0.06em; margin-top: 1mm; }
        svg { max-width: 100%; height: auto; }
    </style>
</head>
<body>

<div class="toolbar">
    <button type="button" class="btn btn-dark" onclick="window.print()">Print</button>
    <button type="button" class="btn" onclick="window.close()">Close</button>
</div>

<div class="sheet">
    @foreach ($labels as $index => $product)
        @php $barcodeValue = $product->barcode ?: ($product->sku ?: (string) $product->id); @endphp
        <div class="label">
            <div class="name">{{ $product->name }}</div>
            <div class="price">{{ $currency }}{{ number_format($product->price, 2) }}</div>
            <svg class="barcode" data-value="{{ $barcodeValue }}" data-index="{{ $index }}"></svg>
            <div class="code">{{ $barcodeValue }}</div>
        </div>
    @endforeach
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
document.querySelectorAll('.barcode').forEach(el => {
    JsBarcode(el, el.dataset.value, {
        format: 'CODE128',
        lineColor: '#0c1222',
        width: {{ $settings['width'] }},
        height: {{ $settings['height'] }},
        displayValue: false,
        margin: 0,
    });
});
@if ($autoPrint)
window.addEventListener('load', () => setTimeout(() => window.print(), 300));
@endif
</script>
</body>
</html>
