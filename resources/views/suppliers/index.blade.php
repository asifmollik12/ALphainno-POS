@extends('layouts.dashboard')
@section('title', 'Suppliers')
@section('content')
@include('partials.page-header', ['title' => 'Suppliers', 'actionUrl' => route('suppliers.create'), 'actionLabel' => '+ Add Supplier'])
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
<table class="w-full text-sm"><thead class="bg-slate-50 text-slate-500"><tr><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Phone</th><th class="px-4 py-3 text-left">Email</th><th class="px-4 py-3 text-right">Due</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
<tbody class="divide-y">@forelse($suppliers as $s)<tr><td class="px-4 py-3 font-medium">{{ $s->name }}</td><td class="px-4 py-3">{{ $s->phone ?: '—' }}</td><td class="px-4 py-3">{{ $s->email ?: '—' }}</td><td class="px-4 py-3 text-right">{{ number_format($s->balance_due,2) }}</td><td class="px-4 py-3 text-right space-x-2"><a href="{{ route('suppliers.edit',$s) }}" class="text-blue-600">Edit</a><form method="POST" action="{{ route('suppliers.destroy',$s) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="py-12 text-center text-slate-400">No suppliers.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $suppliers->links() }}</div>
@endsection
