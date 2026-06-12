@extends('layouts.dashboard')
@section('title', 'Customer Report')
@section('content')
@include('partials.page-header', ['title' => 'Customer Report'])
<div class="bg-white rounded-xl border overflow-hidden shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Customer</th><th class="px-4 py-3 text-left">Phone</th><th class="px-4 py-3 text-right">Sales</th><th class="px-4 py-3 text-right">Balance Due</th></tr></thead><tbody>@foreach($rows as $r)<tr class="border-t"><td class="px-4 py-3">{{ $r->name }}</td><td class="px-4 py-3">{{ $r->phone ?: '—' }}</td><td class="px-4 py-3 text-right">{{ $r->sales_count }}</td><td class="px-4 py-3 text-right">{{ number_format($r->balance_due,2) }}</td></tr>@endforeach</tbody></table></div>
@endsection
