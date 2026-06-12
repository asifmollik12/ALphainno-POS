@extends('layouts.dashboard')
@section('title', 'Sale Returns')
@section('content')
@include('partials.page-header', ['title' => 'Sale Return', 'actionUrl' => route('sale-returns.create'), 'actionLabel' => '+ New Return'])
<div class="bg-white rounded-xl border overflow-hidden shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Reference</th><th class="px-4 py-3 text-left">Customer</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-right">Total</th></tr></thead><tbody>@forelse($returns as $r)<tr class="border-t"><td class="px-4 py-3">{{ $r->reference }}</td><td class="px-4 py-3">{{ $r->customer->name ?? '—' }}</td><td class="px-4 py-3">{{ $r->return_date->format('M d, Y') }}</td><td class="px-4 py-3 text-right">{{ number_format($r->total,2) }}</td></tr>@empty<tr><td colspan="4" class="py-12 text-center text-slate-400">No returns.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $returns->links() }}</div>
@endsection
