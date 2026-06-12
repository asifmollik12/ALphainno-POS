<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sales Invoice — {{ $sale->reference }}</title>
    <style>
        :root {
            --brand: #8b5cf6;
            --brand-dark: #7c3aed;
            --brand-cyan: #1e3a8a;
            --ink: #0c1222;
            --muted: #64748b;
            --line: #e2e8f0;
            --surface: #e0f2fe;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page { size: A4 portrait; margin: 10mm 12mm; }

        html { background: #e2e8f0; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 11pt;
            color: var(--ink);
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print {
            max-width: 210mm;
            margin: 12px auto 0;
            padding: 0 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border: 0;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary { background: #059669; color: #fff; }
        .btn-ghost { background: #fff; color: var(--ink); border: 1px solid var(--line); }

        .invoice-sheet {
            max-width: 210mm;
            width: 100%;
            min-height: 297mm;
            margin: 12px auto 24px;
            padding: 10mm 12mm;
            background: #fff;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.1);
        }

        @media (max-width: 640px) {
            .invoice-sheet {
                min-height: auto;
                padding: 16px 14px;
                margin: 8px auto 16px;
            }
        }

        @media print {
            html { background: #fff; }
            .no-print { display: none !important; }
            .invoice-sheet {
                width: auto;
                max-width: none;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .table-wrap { overflow: visible !important; }
        }

        /* Compact header */
        .header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px 20px;
            align-items: start;
            margin-bottom: 5mm;
            padding-bottom: 4mm;
            border-bottom: 1px solid var(--line);
        }

        @media (max-width: 640px) {
            .header { grid-template-columns: 1fr; gap: 10px; }
        }

        .header-left h1 {
            font-size: 17pt;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 2mm;
            line-height: 1.1;
        }

        .company-name {
            font-size: 11pt;
            font-weight: 700;
            margin-bottom: 1mm;
        }

        .company-details {
            font-size: 9pt;
            color: var(--muted);
            line-height: 1.35;
        }

        .header-right {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 640px) {
            .header-right {
                width: 100%;
                justify-content: space-between;
            }
        }

        .brand-logo {
            max-height: 14mm;
            max-width: 22mm;
            object-fit: contain;
            flex-shrink: 0;
        }

        @media (max-width: 640px) {
            .brand-logo { max-height: 44px; max-width: 80px; }
        }

        .brand-name {
            font-size: 14pt;
            font-weight: 800;
            color: var(--brand);
            white-space: nowrap;
        }

        .invoice-badge {
            padding: 2.5mm 3.5mm;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 6px;
            text-align: right;
            flex-shrink: 0;
        }

        .invoice-badge .ref {
            font-size: 10pt;
            font-weight: 800;
            color: var(--brand-dark);
            white-space: nowrap;
        }

        .invoice-badge .date {
            font-size: 8.5pt;
            color: var(--muted);
            margin-top: 0.5mm;
            white-space: nowrap;
        }

        .parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3mm;
            margin-bottom: 5mm;
        }

        @media (max-width: 640px) {
            .parties { grid-template-columns: 1fr; }
        }

        .party-box {
            border: 1px solid var(--line);
            border-radius: 6px;
            overflow: hidden;
        }

        .party-box .label {
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            padding: 2mm 3mm;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
        }

        .party-box .body {
            padding: 3mm;
            font-size: 9.5pt;
        }

        .party-box .body strong {
            display: block;
            font-size: 10pt;
            margin-bottom: 1mm;
        }

        .section-title {
            font-size: 10pt;
            font-weight: 800;
            margin-bottom: 2mm;
        }

        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 5mm;
        }

        .items-table {
            width: 100%;
            min-width: 560px;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .items-table thead th {
            background: var(--ink);
            color: #fff;
            font-weight: 600;
            text-align: left;
            padding: 2mm 2.5mm;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .items-table tbody td {
            padding: 2mm 2.5mm;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }

        .items-table tbody tr:nth-child(even) td { background: #fafbfc; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .num { font-variant-numeric: tabular-nums; }

        .tax-note {
            display: block;
            font-size: 7.5pt;
            color: var(--muted);
            margin-top: 0.5mm;
        }

        .bottom-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
            gap: 5mm;
        }

        .payment-info { flex: 1 1 180px; min-width: 0; }

        .status-badge {
            display: inline-block;
            padding: 1.5mm 3mm;
            border-radius: 999px;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .status-paid { background: #dcfce7; color: #166534; }
        .status-partial { background: #fef9c3; color: #854d0e; }
        .status-due { background: #fee2e2; color: #991b1b; }

        .meta-list {
            margin-top: 3mm;
            font-size: 9pt;
            color: var(--muted);
        }

        .meta-list div { margin-top: 1mm; }
        .meta-list span { color: var(--ink); font-weight: 600; }

        .totals-box {
            width: 100%;
            max-width: 72mm;
            border: 1px solid var(--line);
            border-radius: 6px;
            overflow: hidden;
            flex-shrink: 0;
        }

        @media (max-width: 640px) {
            .totals-box { max-width: none; margin-left: auto; }
        }

        .totals-box .row {
            display: flex;
            justify-content: space-between;
            padding: 2mm 3mm;
            border-bottom: 1px solid var(--line);
            font-size: 9pt;
        }

        .totals-box .row:last-child { border-bottom: 0; }
        .totals-box .row.subtle { background: var(--surface); color: var(--muted); }

        .totals-box .row.grand {
            background: linear-gradient(135deg, var(--brand-cyan), var(--brand));
            color: #fff;
            font-size: 10pt;
            font-weight: 800;
        }

        .totals-box .row.paid-row { background: #ecfdf5; font-weight: 700; color: #065f46; }
        .totals-box .row.due-row { background: #fff7ed; font-weight: 700; color: #9a3412; }

        .footer {
            margin-top: 8mm;
            padding-top: 4mm;
            border-top: 1px solid var(--line);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-end;
            gap: 12px;
        }

        .signature {
            text-align: center;
            width: 55mm;
            margin-left: auto;
        }

        .signature .line {
            border-top: 1px solid var(--ink);
            margin-bottom: 2mm;
        }

        .signature .caption {
            font-size: 8.5pt;
            color: var(--muted);
            font-weight: 600;
        }

        .thank-you {
            font-size: 8.5pt;
            color: var(--muted);
            max-width: 90mm;
        }

        .thank-you strong { color: var(--ink); display: block; margin-bottom: 1mm; }
    </style>
</head>
<body>
@php
    $currency = $setting->currency ?? '৳';
    $company = $setting->company_name ?? 'Alphainno POS';
    $warehouse = $setting->warehouse_name ?? $company;
    $customerName = $sale->customer->name ?? 'Walk-in customer';
    $shipping = $sale->customer->shipping_address ?? $sale->customer->address ?? $customerName;
    $statusClass = match ($sale->payment_status) {
        'paid' => 'status-paid',
        'partial' => 'status-partial',
        default => 'status-due',
    };
    $totalQty = $sale->items->sum('quantity');
@endphp

<div class="no-print">
    <button type="button" class="btn btn-primary" onclick="window.print()">Print Invoice</button>
    <a href="{{ route('pos.index') }}" class="btn btn-ghost">Back to POS</a>
    <a href="{{ route('sales.index') }}" class="btn btn-ghost">All Sales</a>
</div>

<div class="invoice-sheet">
    <header class="header">
        <div class="header-left">
            <h1>Sales Invoice</h1>
            <div class="company-name">{{ $warehouse }}</div>
            <div class="company-details">
                @if ($setting?->address)<div>{{ $setting->address }}</div>@endif
                @if ($setting?->phone || $setting?->email)
                    <div>
                        @if ($setting?->phone)Phone: {{ $setting->phone }}@endif
                        @if ($setting?->phone && $setting?->email) · @endif
                        @if ($setting?->email)Email: {{ $setting->email }}@endif
                    </div>
                @endif
            </div>
        </div>
        <div class="header-right">
            @if ($setting?->logoUrl())
                <img src="{{ $setting->logoUrl() }}" alt="{{ $company }}" class="brand-logo">
            @else
                <div class="brand-name">{{ $company }}</div>
            @endif
            <div class="invoice-badge">
                <div class="ref">{{ $sale->reference }}</div>
                <div class="date">{{ optional($sale->sale_date)->format('d/m/Y') ?? now()->format('d/m/Y') }}</div>
            </div>
        </div>
    </header>

    <div class="parties">
        <div class="party-box">
            <div class="label">Bill To</div>
            <div class="body">
                <strong>{{ $customerName }}</strong>
                @if ($sale->customer?->mobile)<div>{{ $sale->customer->mobile }}</div>@endif
                @if ($sale->customer?->address)<div>{{ $sale->customer->address }}</div>@endif
            </div>
        </div>
        <div class="party-box">
            <div class="label">Shipping Address</div>
            <div class="body">
                <strong>{{ $customerName }}</strong>
                <div>{{ $shipping }}</div>
            </div>
        </div>
    </div>

    <div class="section-title">Sales Items</div>

    <div class="table-wrap">
        <table class="items-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Product</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Discount</th>
                    <th class="text-right">Tax</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $i => $item)
                <tr>
                    <td class="text-center num">{{ $i + 1 }}</td>
                    <td><strong>{{ $item->product_name }}</strong></td>
                    <td class="text-right num">{{ $currency }}{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-center num">{{ $item->quantity }} ({{ $item->unit ?? 'Pcs' }})</td>
                    <td class="text-right num">{{ $currency }}{{ number_format($item->discount, 2) }}</td>
                    <td class="text-right num">
                        {{ $currency }}{{ number_format($item->tax_amount, 2) }}
                        @if ($item->tax_rate > 0)
                            <span class="tax-note">{{ number_format($item->tax_rate, 0) }}%</span>
                        @endif
                    </td>
                    <td class="text-right num"><strong>{{ $currency }}{{ number_format($item->subtotal, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bottom-grid">
        <div class="payment-info">
            <span class="status-badge {{ $statusClass }}">{{ ucfirst($sale->payment_status) }}</span>
            <div class="meta-list">
                @if ($sale->payment_method)
                    <div>Method: <span>{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</span></div>
                @endif
                @if ($sale->warehouse)
                    <div>Warehouse: <span>{{ $sale->warehouse }}</span></div>
                @endif
                <div>Items: <span>{{ $totalQty }}</span></div>
            </div>
        </div>

        <div class="totals-box">
            <div class="row subtle">
                <span>Subtotal</span>
                <span class="num">{{ $currency }}{{ number_format($sale->subtotal ?: $sale->items->sum(fn ($i) => $i->unit_price * $i->quantity), 2) }}</span>
            </div>
            <div class="row subtle">
                <span>Discount</span>
                <span class="num">− {{ $currency }}{{ number_format($sale->discount_amount, 2) }}</span>
            </div>
            <div class="row subtle">
                <span>Tax</span>
                <span class="num">{{ $currency }}{{ number_format($sale->tax_amount, 2) }}</span>
            </div>
            <div class="row grand">
                <span>Grand Total</span>
                <span class="num">{{ $currency }}{{ number_format($sale->total, 2) }}</span>
            </div>
            <div class="row paid-row">
                <span>Paid</span>
                <span class="num">{{ $currency }}{{ number_format($sale->paid_amount, 2) }}</span>
            </div>
            @if ($sale->due_amount > 0)
            <div class="row due-row">
                <span>Due</span>
                <span class="num">{{ $currency }}{{ number_format($sale->due_amount, 2) }}</span>
            </div>
            @endif
        </div>
    </div>

    <footer class="footer">
        <div class="thank-you">
            <strong>Thank you for your business!</strong>
            Computer-generated invoice.
        </div>
        <div class="signature">
            <div class="line"></div>
            <div class="caption">Authorized Signature</div>
        </div>
    </footer>
</div>
</body>
</html>
