@extends('layouts.dashboard')
@section('title', 'Customers')
@section('content')
@include('partials.page-header', ['title' => 'Customers', 'actionUrl' => route('customers.create'), 'actionLabel' => '+ Add Customer'])
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
<table class="w-full text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Phone</th><th class="px-4 py-3 text-left">Email</th><th class="px-4 py-3 text-right">Due</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
<tbody class="divide-y">@forelse($customers as $c)<tr><td class="px-4 py-3 font-medium">{{ $c->name }}</td><td class="px-4 py-3">{{ $c->phone ?: '—' }}</td><td class="px-4 py-3">{{ $c->email ?: '—' }}</td><td class="px-4 py-3 text-right">{{ number_format($c->balance_due,2) }}</td><td class="px-4 py-3 text-right space-x-2"><a href="{{ route('customers.edit',$c) }}" class="text-blue-600">Edit</a><form method="POST" action="{{ route('customers.destroy',$c) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="py-12 text-center text-slate-400">No customers.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $customers->links() }}</div>
@endsection
