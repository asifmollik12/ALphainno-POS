<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sales Invoice — {{ $sale->reference }}</title>
    <style>
        :root {
            --brand: #6366f1;
            --brand-dark: #4f46e5;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --surface: #f8fafc;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        html { background: #e2e8f0; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 11pt;
            color: var(--ink);
            line-height: 1.45;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print {
            max-width: 210mm;
            margin: 16px auto 0;
            padding: 0 12px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            border: 0;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary { background: #059669; color: #fff; }
        .btn-ghost { background: #fff; color: var(--ink); border: 1px solid var(--line); }

        .invoice-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 16px auto 24px;
            padding: 12mm 14mm;
            background: #fff;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.12);
        }

        @media print {
            html { background: #fff; }
            .no-print { display: none !important; }
            .invoice-sheet {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }

        .print-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9pt;
            color: var(--muted);
            margin-bottom: 6mm;
            padding-bottom: 3mm;
            border-bottom: 1px solid var(--line);
        }

        .print-meta .ref { font-weight: 700; color: var(--ink); letter-spacing: 0.02em; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 8mm;
        }

        .header-left h1 {
            font-size: 22pt;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 4mm;
            color: var(--ink);
        }

        .company-name {
            font-size: 12pt;
            font-weight: 700;
            margin-bottom: 2mm;
        }

        .company-details {
            font-size: 10pt;
            color: var(--muted);
            max-width: 85mm;
        }

        .company-details div + div { margin-top: 1mm; }

        .header-right { text-align: right; min-width: 55mm; }

        .brand-logo {
            max-height: 18mm;
            max-width: 50mm;
            object-fit: contain;
            margin-bottom: 3mm;
            margin-left: auto;
        }

        .brand-name {
            font-size: 20pt;
            font-weight: 800;
            color: var(--brand);
            letter-spacing: -0.03em;
        }

        .invoice-badge {
            margin-top: 4mm;
            padding: 3mm 4mm;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 6px;
            text-align: left;
            display: inline-block;
        }

        .invoice-badge .ref {
            font-size: 11pt;
            font-weight: 800;
            color: var(--brand-dark);
        }

        .invoice-badge .date {
            font-size: 9.5pt;
            color: var(--muted);
            margin-top: 1mm;
        }

        .parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4mm;
            margin-bottom: 8mm;
        }

        .party-box {
            border: 1px solid var(--line);
            border-radius: 6px;
            overflow: hidden;
        }

        .party-box .label {
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            padding: 2.5mm 4mm;
            font-size: 9pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
        }

        .party-box .body {
            padding: 4mm;
            font-size: 10pt;
            min-height: 18mm;
        }

        .party-box .body strong {
            display: block;
            font-size: 11pt;
            margin-bottom: 1.5mm;
        }

        .section-title {
            font-size: 11pt;
            font-weight: 800;
            margin-bottom: 3mm;
            color: var(--ink);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6mm;
            font-size: 9.5pt;
        }

        .items-table thead th {
            background: var(--ink);
            color: #fff;
            font-weight: 600;
            text-align: left;
            padding: 2.5mm 3mm;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .items-table thead th:first-child { border-radius: 4px 0 0 0; }
        .items-table thead th:last-child { border-radius: 0 4px 0 0; }

        .items-table tbody td {
            padding: 2.5mm 3mm;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }

        .items-table tbody tr:nth-child(even) td { background: #fafbfc; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .num { font-variant-numeric: tabular-nums; }

        .tax-note {
            display: block;
            font-size: 8pt;
            color: var(--muted);
            margin-top: 0.5mm;
        }

        .bottom-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8mm;
            margin-top: 4mm;
        }

        .payment-info { flex: 1; }

        .status-badge {
            display: inline-block;
            padding: 2mm 4mm;
            border-radius: 999px;
            font-size: 9pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-paid { background: #dcfce7; color: #166534; }
        .status-partial { background: #fef9c3; color: #854d0e; }
        .status-due { background: #fee2e2; color: #991b1b; }

        .meta-list {
            margin-top: 4mm;
            font-size: 9.5pt;
            color: var(--muted);
        }

        .meta-list div { margin-top: 1.5mm; }
        .meta-list span { color: var(--ink); font-weight: 600; }

        .totals-box {
            width: 72mm;
            border: 1px solid var(--line);
            border-radius: 6px;
            overflow: hidden;
        }

        .totals-box .row {
            display: flex;
            justify-content: space-between;
            padding: 2.5mm 4mm;
            border-bottom: 1px solid var(--line);
            font-size: 9.5pt;
        }

        .totals-box .row:last-child { border-bottom: 0; }

        .totals-box .row.subtle { background: var(--surface); color: var(--muted); }

        .totals-box .row.grand {
            background: var(--brand);
            color: #fff;
            font-size: 11pt;
            font-weight: 800;
        }

        .totals-box .row.paid-row {
            background: #ecfdf5;
            font-weight: 700;
            color: #065f46;
        }

        .totals-box .row.due-row {
            background: #fff7ed;
            font-weight: 700;
            color: #9a3412;
        }

        .footer {
            margin-top: 12mm;
            padding-top: 6mm;
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature {
            text-align: center;
            width: 55mm;
        }

        .signature .line {
            border-top: 1px solid var(--ink);
            margin-bottom: 2mm;
        }

        .signature .caption {
            font-size: 9pt;
            color: var(--muted);
            font-weight: 600;
        }

        .thank-you {
            font-size: 9pt;
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
    <div class="print-meta">
        <span>{{ now()->format('d/m/Y, H:i') }}</span>
        <span class="ref">{{ $sale->reference }}</span>
    </div>

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
                <div class="date">Order Date: {{ optional($sale->sale_date)->format('d/m/Y') ?? now()->format('d/m/Y') }}</div>
            </div>
        </div>
    </header>

    <div class="parties">
        <div class="party-box">
            <div class="label">Bill To</div>
            <div class="body">
                <strong>{{ $customerName }}</strong>
                @if ($sale->customer?->mobile)<div>{{ $sale->customer->mobile }}</div>@endif
                @if ($sale->customer?->email)<div>{{ $sale->customer->email }}</div>@endif
                @if ($sale->customer?->address)<div>{{ $sale->customer->address }}</div>@endif
            </div>
        </div>
        <div class="party-box">
            <div class="label">Shipping Address</div>
            <div class="body">
                <strong>{{ $customerName }}</strong>
                <div>{{ $shipping }}</div>
                @if ($sale->customer?->shipping_city)
                    <div>{{ $sale->customer->shipping_city }}{{ $sale->customer->shipping_country ? ', '.$sale->customer->shipping_country : '' }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="section-title">Sales Items</div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width:6%">No.</th>
                <th style="width:28%">Product</th>
                <th class="text-right" style="width:14%">Unit Price</th>
                <th class="text-center" style="width:12%">Qty</th>
                <th class="text-right" style="width:12%">Discount</th>
                <th class="text-right" style="width:14%">Tax</th>
                <th class="text-right" style="width:14%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $i => $item)
            <tr>
                <td class="text-center num">{{ $i + 1 }}</td>
                <td><strong>{{ $item->product_name }}</strong></td>
                <td class="text-right num">{{ $currency }}{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-center num">{{ $item->quantity }} <span style="color:var(--muted);">({{ $item->unit ?? 'Pcs' }})</span></td>
                <td class="text-right num">{{ $currency }}{{ number_format($item->discount, 2) }}</td>
                <td class="text-right num">
                    {{ $currency }}{{ number_format($item->tax_amount, 2) }}
                    @if ($item->tax_rate > 0)
                        <span class="tax-note">Tax ({{ number_format($item->tax_rate, 0) }}%)</span>
                    @endif
                </td>
                <td class="text-right num"><strong>{{ $currency }}{{ number_format($item->subtotal, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="bottom-grid">
        <div class="payment-info">
            <span class="status-badge {{ $statusClass }}">Payment: {{ ucfirst($sale->payment_status) }}</span>
            <div class="meta-list">
                @if ($sale->payment_method)
                    <div>Payment Method: <span>{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</span></div>
                @endif
                @if ($sale->payment_reference)
                    <div>Reference: <span>{{ $sale->payment_reference }}</span></div>
                @endif
                @if ($sale->warehouse)
                    <div>Warehouse: <span>{{ $sale->warehouse }}</span></div>
                @endif
                @if ($sale->delivery_status)
                    <div>Delivery: <span>{{ ucfirst($sale->delivery_status) }}</span></div>
                @endif
                <div>Total Items: <span>{{ $totalQty }}</span></div>
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
            This is a computer-generated invoice and is valid without signature unless required by law.
        </div>
        <div class="signature">
            <div class="line"></div>
            <div class="caption">Authorized Signature</div>
        </div>
    </footer>
</div>
</body>
</html>
