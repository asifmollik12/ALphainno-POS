@extends('layouts.dashboard')
@section('title', 'Settings')
@section('content')
@include('partials.page-header', ['title' => 'Settings', 'subtitle' => 'Shop profile and preferences'])
<form method="POST" action="{{ route('settings.update') }}" class="bg-white rounded-xl border p-6 max-w-xl space-y-4 shadow-sm">@csrf @method('PUT')
<div><label class="text-sm font-medium">Company name</label><input name="company_name" value="{{ old('company_name',$setting->company_name) }}" class="w-full mt-1 rounded-lg border-slate-300"></div>
<div><label class="text-sm font-medium">Warehouse name</label><input name="warehouse_name" value="{{ old('warehouse_name',$setting->warehouse_name) }}" class="w-full mt-1 rounded-lg border-slate-300" placeholder="Main Warehouse"></div>
<div class="grid sm:grid-cols-2 gap-4">
<div><label class="text-sm font-medium">Currency symbol</label><input name="currency" value="{{ old('currency',$setting->currency) }}" class="w-full mt-1 rounded-lg border-slate-300"></div>
<div><label class="text-sm font-medium">Default tax rate (%)</label><input type="number" step="0.01" name="default_tax_rate" value="{{ old('default_tax_rate',$setting->default_tax_rate ?? 0) }}" class="w-full mt-1 rounded-lg border-slate-300"></div>
</div>
<div><label class="text-sm font-medium">Phone</label><input name="phone" value="{{ old('phone',$setting->phone) }}" class="w-full mt-1 rounded-lg border-slate-300"></div>
<div><label class="text-sm font-medium">Email</label><input type="email" name="email" value="{{ old('email',$setting->email) }}" class="w-full mt-1 rounded-lg border-slate-300"></div>
<div><label class="text-sm font-medium">Address</label><textarea name="address" rows="3" class="w-full mt-1 rounded-lg border-slate-300">{{ old('address',$setting->address) }}</textarea></div>
<button class="px-5 py-2 bg-violet-600 text-white rounded-lg">Save settings</button></form>
@endsection
