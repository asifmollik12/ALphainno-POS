@extends('layouts.dashboard')
@section('title', 'Edit Account')
@section('content')
@include('partials.page-header', ['title' => 'Edit Account'])
<form method="POST" action="{{ route('accounts.update', $account) }}" class="bg-white rounded-xl border p-6 max-w-xl space-y-4">@csrf @method('PUT')
<div><label class="text-sm font-medium">Name</label><input name="name" value="{{ old('name',$account->name) }}" required class="w-full mt-1 rounded-lg border-slate-300"></div>
<div><label class="text-sm font-medium">Code</label><input name="code" value="{{ old('code',$account->code) }}" class="w-full mt-1 rounded-lg border-slate-300"></div>
<div><label class="text-sm font-medium">Type</label><select name="type" class="w-full mt-1 rounded-lg border-slate-300">@foreach(['asset','liability','equity','income','expense'] as $t)<option value="{{ $t }}" @selected($account->type===$t)>{{ ucfirst($t) }}</option>@endforeach</select></div>
<button class="px-5 py-2 bg-violet-600 text-white rounded-lg">Save</button></form>
@endsection
