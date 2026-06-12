@extends('layouts.dashboard')
@section('title', 'Accounts')
@section('content')
@include('partials.page-header', ['title' => 'Chart of Accounts', 'actionUrl' => route('accounts.create'), 'actionLabel' => '+ Add Account'])
<div class="bg-white rounded-xl border overflow-hidden shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Code</th><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-right">Balance</th><th class="px-4 py-3 text-right">Actions</th></tr></thead><tbody>@forelse($accounts as $a)<tr class="border-t"><td class="px-4 py-3">{{ $a->code }}</td><td class="px-4 py-3 font-medium">{{ $a->name }}</td><td class="px-4 py-3 capitalize">{{ $a->type }}</td><td class="px-4 py-3 text-right">{{ number_format($a->current_balance,2) }}</td><td class="px-4 py-3 text-right"><a href="{{ route('accounts.edit',$a) }}" class="text-blue-600">Edit</a></td></tr>@empty<tr><td colspan="5" class="py-12 text-center text-slate-400">No accounts.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $accounts->links() }}</div>
@endsection
