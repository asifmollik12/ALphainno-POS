@extends('layouts.dashboard')
@section('title', 'Trial Balance')
@section('content')
@include('partials.page-header', ['title' => 'Trial Balance'])
<div class="bg-white rounded-xl border overflow-hidden shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Account</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-right">Balance</th></tr></thead><tbody>@foreach($accounts as $a)<tr class="border-t"><td class="px-4 py-3">{{ $a->code }} — {{ $a->name }}</td><td class="px-4 py-3 capitalize">{{ $a->type }}</td><td class="px-4 py-3 text-right">{{ number_format($a->current_balance,2) }}</td></tr>@endforeach</tbody></table></div>
@endsection
