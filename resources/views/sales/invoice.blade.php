<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $sale->reference }} — Invoice</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page { size: A4 portrait; margin: 12mm; }

        html { background: #f1f5f9; }

        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            font-size: 14px;
            color: #1e293b;
            line-height: 1.5;
        }

        .toolbar {
            max-width: 800px;
            margin: 12px auto 0;
            padding: 0 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .btn {
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-green { background: #059669; color: #fff; border-color: #059669; }

        .sheet {
            max-width: 800px;
            width: 100%;
            margin: 12px auto 24px;
            padding: 20px 16px;
            background: #fff;
            border: 1px solid #e2e8f0;
        }

        @media (min-width: 640px) {
            .sheet { padding: 28px 32px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        }

        @media print {
            html { background: #fff; }
            .toolbar { display: none !important; }
            .sheet {
                max-width: none;
                margin: 0;
                padding: 0;
                border: 0;
                box-shadow: none;
            }
            .table-wrap { overflow: visible !important; }
        }

        /* Header */
        .top {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 16px;
            border-bottom: 2px solid #1e293b;
            margin-bottom: 16px;
        }

        .top h1 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .top .shop { font-weight: 600; }
        .top .muted { color: #64748b; font-size: 13px; margin-top: 4px; }

        .top-right { text-align: left; }
        @media (min-width: 640px) { .top-right { text-align: right; } }

        .logo { max-height: 48px; max-width: 140px; object-fit: contain; margin-bottom: 8px; }
        .inv-no { font-size: 15px; font-weight: 700; color: #4f46e5; }
        .inv-date { font-size: 13px; color: #64748b; }

        /* Customer row */
        .info-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        @media (min-width: 640px) {
            .info-row { grid-template-columns: 1fr 1fr; }
        }

        .info-row dt { color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 2px; }
        .info-row dd { font-weight: 600; }

        /* Table */
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 16px;
        }

        table { width: 100%; min-width: 520px; border-collapse: collapse; font-size: 13px; }

        th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 8px 6px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #64748b;
            white-space: nowrap;
        }

        td { padding: 10px 6px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .r { text-align: right; }
        .c { text-align: center; }
        .num { font-variant-numeric: tabular-nums; }

        /* Mobile item cards (hidden on print & desktop table) */
        .item-cards { display: none; }

        @media (max-width: 639px) {
            .table-wrap { display: none; }
            .item-cards { display: block; }
            .item-card {
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 12px;
                margin-bottom: 8px;
            }
            .item-card .name { font-weight: 600; margin-bottom: 6px; }
            .item-card .grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 4px 12px;
                font-size: 12px;
                color: #64748b;
            }
            .item-card .grid span:last-child { text-align: right; color: #1e293b; font-weight: 600; }
        }

        @media print {
            .table-wrap { display: block !important; }
            .item-cards { display: none !important; }
        }

        /* Totals */
        .bottom {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 8px;
        }

        @media (min-width: 640px) {
            .bottom { flex-direction: row; justify-content: space-between; align-items: flex-start; }
        }

        .pay-note { font-size: 13px; color: #64748b; }
        .pay-note strong { color: #166534; }

        .totals {
            width: 100%;
            max-width: 280px;
            margin-left: auto;
        }

        @media (min-width: 640px) { .totals { width: 260px; } }

        .totals .line {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }

        .totals .line.total {
            border-top: 2px solid #1e293b;
            border-bottom: 0;
            margin-top: 4px;
            padding-top: 10px;
            font-size: 16px;
            font-weight: 700;
        }

        .totals .line.paid { color: #059669; font-weight: 600; }
        .totals .line.due { color: #dc2626; font-weight: 600; }

        .foot {
            margin-top: 28px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 16px;
            font-size: 12px;
            color: #64748b;
        }

        .sign { width: 160px; text-align: center; margin-left: auto; }
        .sign .line { border-top: 1px solid #94a3b8; margin-bottom: 4px; }
    </style>
</head>
<body>
@php
    $currency = $setting->currency ?? '৳';
    $shop = $setting->warehouse_name ?? $setting->company_name ?? 'Alphainno POS';
    $customer = $sale->customer->name ?? 'Walk-in customer';
    $subtotal = $sale->subtotal ?: $sale->items->sum(fn ($i) => $i->unit_price * $i->quantity);
@endphp

<div class="toolbar no-print">
    <button type="button" class="btn btn-green" onclick="window.print()">Print</button>
    <a href="{{ route('pos.index') }}" class="btn">POS</a>
    <a href="{{ route('sales.index') }}" class="btn">Sales</a>
</div>

<div class="sheet">
    <header class="top">
        <div>
            <h1>Invoice</h1>
            <div class="shop">{{ $shop }}</div>
            <div class="muted">
                @if ($setting?->address){{ $setting->address }}<br>@endif
                @if ($setting?->phone){{ $setting->phone }}@endif
                @if ($setting?->phone && $setting?->email) · @endif
                @if ($setting?->email){{ $setting->email }}@endif
            </div>
        </div>
        <div class="top-right">
            @if ($setting?->logoUrl())
                <img src="{{ $setting->logoUrl() }}" alt="" class="logo">
            @endif
            <div class="inv-no">{{ $sale->reference }}</div>
            <div class="inv-date">{{ optional($sale->sale_date)->format('d M Y') ?? now()->format('d M Y') }}</div>
        </div>
    </header>

    <dl class="info-row">
        <div>
            <dt>Customer</dt>
            <dd>{{ $customer }}</dd>
            @if ($sale->customer?->mobile)<dd style="font-weight:400;color:#64748b;">{{ $sale->customer->mobile }}</dd>@endif
        </div>
        <div>
            <dt>Payment</dt>
            <dd>{{ ucfirst($sale->payment_status) }} · {{ ucfirst($sale->payment_method ?? 'cash') }}</dd>
        </div>
    </dl>

    {{-- Desktop / print table --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="r">Price</th>
                    <th class="c">Qty</th>
                    <th class="r">Tax</th>
                    <th class="r">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $i => $item)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td class="r num">{{ $currency }}{{ number_format($item->unit_price, 2) }}</td>
                    <td class="c num">{{ $item->quantity }}</td>
                    <td class="r num">{{ $currency }}{{ number_format($item->tax_amount, 2) }}</td>
                    <td class="r num"><strong>{{ $currency }}{{ number_format($item->subtotal, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="item-cards">
        @foreach ($sale->items as $i => $item)
        <div class="item-card">
            <div class="name">{{ $i + 1 }}. {{ $item->product_name }}</div>
            <div class="grid">
                <span>Price</span><span class="num">{{ $currency }}{{ number_format($item->unit_price, 2) }}</span>
                <span>Qty</span><span>{{ $item->quantity }}</span>
                <span>Tax</span><span class="num">{{ $currency }}{{ number_format($item->tax_amount, 2) }}</span>
                <span>Total</span><span class="num">{{ $currency }}{{ number_format($item->subtotal, 2) }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <div class="bottom">
        <div class="pay-note">
            Payment status: <strong>{{ ucfirst($sale->payment_status) }}</strong>
            @if ($sale->due_amount > 0)
                · Due {{ $currency }}{{ number_format($sale->due_amount, 2) }}
            @endif
        </div>
        <div class="totals">
            <div class="line"><span>Subtotal</span><span class="num">{{ $currency }}{{ number_format($subtotal, 2) }}</span></div>
            @if ($sale->discount_amount > 0)
            <div class="line"><span>Discount</span><span class="num">− {{ $currency }}{{ number_format($sale->discount_amount, 2) }}</span></div>
            @endif
            <div class="line"><span>Tax</span><span class="num">{{ $currency }}{{ number_format($sale->tax_amount, 2) }}</span></div>
            <div class="line total"><span>Total</span><span class="num">{{ $currency }}{{ number_format($sale->total, 2) }}</span></div>
            <div class="line paid"><span>Paid</span><span class="num">{{ $currency }}{{ number_format($sale->paid_amount, 2) }}</span></div>
            @if ($sale->due_amount > 0)
            <div class="line due"><span>Due</span><span class="num">{{ $currency }}{{ number_format($sale->due_amount, 2) }}</span></div>
            @endif
        </div>
    </div>

    <footer class="foot">
        <span>Thank you!</span>
        <div class="sign">
            <div class="line"></div>
            Signature
        </div>
    </footer>
</div>
</body>
</html>
