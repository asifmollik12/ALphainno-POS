@extends('layouts.dashboard')
@section('title', 'Sale Report')
@section('content')
@include('partials.page-header', ['title' => 'Sale Report'])
<div class="bg-white rounded-xl border overflow-hidden shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Reference</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3 text-right">Paid</th><th class="px-4 py-3 text-right">Due</th></tr></thead><tbody>@foreach($rows as $r)<tr class="border-t"><td class="px-4 py-3">{{ $r->reference }}</td><td class="px-4 py-3">{{ optional($r->sale_date)->format('M d, Y') ?? '—' }}</td><td class="px-4 py-3 text-right">{{ number_format($r->total,2) }}</td><td class="px-4 py-3 text-right">{{ number_format($r->paid_amount,2) }}</td><td class="px-4 py-3 text-right">{{ number_format($r->due_amount,2) }}</td></tr>@endforeach</tbody></table></div>
@endsection
