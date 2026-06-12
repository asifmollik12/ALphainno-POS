@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
@php $currency = $shopSetting->currency ?? '৳'; @endphp

<div class="flex flex-wrap items-start justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p class="text-slate-500 text-sm mt-1">Real-time overview of sales, finance, and operations</p>
    </div>
    <form method="GET" class="flex items-center gap-2 bg-white rounded-lg border border-slate-200 px-3 py-2 text-sm">
        <input type="date" name="from" value="{{ $from->toDateString() }}" class="border-0 focus:ring-0 p-0 text-slate-600">
        <span class="text-slate-400">to</span>
        <input type="date" name="to" value="{{ $to->toDateString() }}" class="border-0 focus:ring-0 p-0 text-slate-600">
        <button type="submit" class="ml-2 px-3 py-1 bg-slate-900 text-white rounded-md text-xs">Apply</button>
    </form>
</div>

{{-- KPI Cards --}}
<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    @foreach ([
        ['label' => 'Total Sale Amount', 'value' => $metrics['total_sale'], 'color' => 'emerald', 'spark' => [10,20,15,30,25,40,35]],
        ['label' => 'Total Sale Due', 'value' => $metrics['total_sale_due'], 'color' => 'rose', 'spark' => [30,28,32,30,31,29,30]],
        ['label' => 'Total Purchase Amount', 'value' => $metrics['total_purchase'], 'color' => 'emerald', 'spark' => [5,8,12,10,18,15,22]],
        ['label' => 'Total Purchase Due', 'value' => $metrics['total_purchase_due'], 'color' => 'rose', 'spark' => [8,12,10,15,14,18,16]],
    ] as $card)
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <div class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $card['label'] }}</div>
        <div class="text-2xl font-bold text-slate-900 mt-1">{{ $currency }}{{ number_format($card['value'], 2) }}</div>
        <div class="h-10 mt-3"><canvas class="spark-{{ $loop->index }}"></canvas></div>
    </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
    {{-- Sales donut --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <h3 class="font-semibold text-slate-800 mb-4">Sales (Paid / Due / Return)</h3>
        <div class="flex items-center gap-6">
            <div class="w-44 h-44"><canvas id="salesDonut"></canvas></div>
            <div class="space-y-2 text-sm">
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500"></span> Paid: {{ $currency }}{{ number_format($metrics['sale_paid'], 2) }}</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-400"></span> Due: {{ $currency }}{{ number_format($metrics['total_sale_due'], 2) }}</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-500"></span> Return: {{ $currency }}{{ number_format($metrics['sale_return'], 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Purchases donut --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <h3 class="font-semibold text-slate-800 mb-4">Purchases (Paid / Due / Return)</h3>
        <div class="flex items-center gap-6">
            <div class="w-44 h-44"><canvas id="purchaseDonut"></canvas></div>
            <div class="space-y-2 text-sm">
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-violet-500"></span> Paid: {{ $currency }}{{ number_format($metrics['purchase_paid'], 2) }}</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-400"></span> Due: {{ $currency }}{{ number_format($metrics['total_purchase_due'], 2) }}</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-500"></span> Return: {{ $currency }}{{ number_format($metrics['purchase_return'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <h3 class="font-semibold text-slate-800 mb-4">Sales vs Purchases (Monthly)</h3>
        <div class="h-72"><canvas id="monthlyChart"></canvas></div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <h3 class="font-semibold text-slate-800 mb-4">Inventory Snapshot</h3>
        <div class="space-y-4">
            <div class="flex justify-between py-3 border-b border-slate-100"><span class="text-slate-500">Products</span><span class="font-semibold">{{ $inventory['total_products'] }}</span></div>
            <div class="flex justify-between py-3 border-b border-slate-100"><span class="text-slate-500">Stock Value</span><span class="font-semibold">{{ $currency }}{{ number_format($inventory['total_stock_value'], 2) }}</span></div>
            <div class="flex justify-between py-3"><span class="text-slate-500">Shortage Items</span><span class="font-semibold text-amber-600">{{ $inventory['shortage_count'] }}</span></div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
    <h3 class="font-semibold text-slate-800 mb-4">Transactions by Account</h3>
    @if ($transactions->isEmpty())
        <p class="text-slate-400 text-sm py-8 text-center">No transactions yet.</p>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-slate-500 border-b border-slate-100">
                <tr>
                    <th class="text-left py-2 font-medium">Date</th>
                    <th class="text-left py-2 font-medium">Account</th>
                    <th class="text-left py-2 font-medium">Description</th>
                    <th class="text-right py-2 font-medium">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach ($transactions as $tx)
                <tr>
                    <td class="py-2.5">{{ $tx->transaction_date->format('M d, Y') }}</td>
                    <td class="py-2.5">{{ $tx->account->name ?? '—' }}</td>
                    <td class="py-2.5 text-slate-600">{{ $tx->description ?? $tx->reference ?? '—' }}</td>
                    <td class="py-2.5 text-right font-medium {{ $tx->type === 'credit' ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $tx->type === 'credit' ? '+' : '-' }}{{ $currency }}{{ number_format($tx->amount, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const monthly = @json($monthly);
const salesDonut = [@json($metrics['sale_paid']), @json($metrics['total_sale_due']), @json($metrics['sale_return'])];
const purchaseDonut = [@json($metrics['purchase_paid']), @json($metrics['total_purchase_due']), @json($metrics['purchase_return'])];

new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: monthly.map(r => r.label),
        datasets: [
            { label: 'Sales', data: monthly.map(r => r.sales), borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.4 },
            { label: 'Purchases', data: monthly.map(r => r.purchases), borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.1)', fill: true, tension: 0.4 },
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('salesDonut'), {
    type: 'doughnut',
    data: { labels: ['Paid','Due','Return'], datasets: [{ data: salesDonut, backgroundColor: ['#3b82f6','#fbbf24','#ef4444'] }] },
    options: { cutout: '65%', plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('purchaseDonut'), {
    type: 'doughnut',
    data: { labels: ['Paid','Due','Return'], datasets: [{ data: purchaseDonut, backgroundColor: ['#8b5cf6','#fbbf24','#ef4444'] }] },
    options: { cutout: '65%', plugins: { legend: { display: false } } }
});

document.querySelectorAll('[class^="spark-"]').forEach((el, i) => {
    const data = [[10,20,15,30,25,40,35],[30,28,32,30,31,29,30],[5,8,12,10,18,15,22],[8,12,10,15,14,18,16]][i];
    new Chart(el, { type: 'line', data: { labels: data.map((_,j)=>j), datasets: [{ data, borderColor: i%2?'#f43f5e':'#10b981', borderWidth: 2, pointRadius: 0, fill: false, tension: 0.4 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } } });
});
</script>
@endpush
