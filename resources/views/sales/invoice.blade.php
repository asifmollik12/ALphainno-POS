<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sales Invoice — {{ $sale->reference }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #222; margin: 24px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
        .brand { text-align: right; }
        .brand h1 { margin: 0; font-size: 28px; color: #7c3aed; }
        .meta { margin-top: 8px; font-size: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
        .text-right { text-align: right; }
        .summary td { font-weight: bold; }
        .footer { margin-top: 40px; display: flex; justify-content: space-between; }
        .paid { font-size: 18px; font-weight: bold; }
        @media print { .no-print { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
@php $currency = $setting->currency ?? '৳'; @endphp

<div class="no-print" style="margin-bottom:16px;">
    <button onclick="window.print()" style="padding:8px 16px;background:#059669;color:#fff;border:0;border-radius:6px;cursor:pointer;">Print Invoice</button>
    <a href="{{ route('pos.index') }}" style="margin-left:8px;">Back to POS</a>
</div>

<div class="header">
    <div>
        <h2 style="margin:0 0 8px;">Sales Invoice</h2>
        <strong>{{ $setting->warehouse_name ?? $setting->company_name ?? 'Alphainno POS' }}</strong><br>
        {{ $setting->address ?? '' }}<br>
        @if($setting?->phone) Phone: {{ $setting->phone }}<br>@endif
        @if($setting?->email) Email: {{ $setting->email }}@endif
    </div>
    <div class="brand">
        @if ($setting?->logoUrl())
            <img src="{{ $setting->logoUrl() }}" alt="Logo" style="max-height:56px;margin-bottom:8px;">
        @else
            <h1>{{ $setting->company_name ?? 'QuickPOS' }}</h1>
        @endif
        <div class="meta">
            <div><strong>{{ $sale->reference }}</strong></div>
            <div>Order Date: {{ optional($sale->sale_date)->format('n/j/Y') ?? now()->format('n/j/Y') }}</div>
        </div>
    </div>
</div>

<table style="border:0;margin-bottom:16px;">
    <tr style="border:0;">
        <td style="border:0;width:50%;vertical-align:top;">
            <strong>Customer</strong><br>
            {{ $sale->customer->name ?? 'Walk-in customer' }}<br>
            @if($sale->customer?->mobile) {{ $sale->customer->mobile }}<br>@endif
            @if($sale->customer?->address) {{ $sale->customer->address }}@endif
        </td>
        <td style="border:0;width:50%;vertical-align:top;">
            <strong>Shipping Address</strong><br>
            {{ $sale->customer->shipping_address ?? $sale->customer->address ?? 'Walk-in customer' }}
        </td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Product</th>
            <th class="text-right">Unit Price</th>
            <th class="text-right">Quantity</th>
            <th class="text-right">Discount</th>
            <th class="text-right">Tax</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sale->items as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->product_name }}</td>
            <td class="text-right">{{ $currency }}{{ number_format($item->unit_price, 2) }}</td>
            <td class="text-right">{{ $item->quantity }} ({{ $item->unit ?? 'Pcs' }})</td>
            <td class="text-right">{{ $currency }}{{ number_format($item->discount, 2) }}</td>
            <td class="text-right">{{ $currency }}{{ number_format($item->tax_amount, 2) }}@if($item->tax_rate > 0) ({{ $item->tax_rate }}%)@endif</td>
            <td class="text-right">{{ $currency }}{{ number_format($item->subtotal, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot class="summary">
        <tr>
            <td colspan="3" class="text-right">Total Quantity</td>
            <td class="text-right">{{ $sale->items->sum('quantity') }}</td>
            <td class="text-right">{{ $currency }}{{ number_format($sale->discount_amount, 2) }}</td>
            <td class="text-right">{{ $currency }}{{ number_format($sale->tax_amount, 2) }}</td>
            <td class="text-right">{{ $currency }}{{ number_format($sale->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="6" class="text-right">Amount Paid</td>
            <td class="text-right">{{ $currency }}{{ number_format($sale->paid_amount, 2) }}</td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    <div class="paid">{{ ucfirst($sale->payment_status) }}</div>
    <div style="text-align:right;">
        <div style="border-top:1px solid #999;width:200px;margin-left:auto;padding-top:4px;">Authorized Signature</div>
    </div>
</div>
</body>
</html>
