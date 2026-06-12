@extends('layouts.dashboard')
@section('title', 'Balance Sheet')
@section('content')
@include('partials.page-header', ['title' => 'Balance Sheet'])
<div class="grid md:grid-cols-2 gap-4">@foreach(['asset'=>'Assets','liability'=>'Liabilities','equity'=>'Equity'] as $type=>$label)<div class="bg-white rounded-xl border p-5 shadow-sm"><h3 class="font-semibold mb-3">{{ $label }}</h3>@forelse(($accounts[$type] ?? collect()) as $a)<div class="flex justify-between py-2 border-b text-sm"><span>{{ $a->name }}</span><span>{{ number_format($a->current_balance,2) }}</span></div>@empty<p class="text-slate-400 text-sm">No accounts</p>@endforelse</div>@endforeach</div>
@endsection
