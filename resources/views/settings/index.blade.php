@extends('layouts.dashboard')
@section('title', 'Settings')
@section('content')
@include('partials.page-header', ['title' => 'Settings', 'subtitle' => 'Company profile, logo, tax and warehouse'])
<form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="bg-white rounded-xl border p-6 max-w-2xl space-y-5 shadow-sm">
    @csrf @method('PUT')

    <div class="flex flex-wrap gap-6 items-start">
        <div class="flex-shrink-0">
            <label class="text-sm font-medium text-slate-700 block mb-2">Company logo</label>
            <div class="w-28 h-28 rounded-xl border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden bg-slate-50">
                @if ($setting->logoUrl())
                    <img src="{{ $setting->logoUrl() }}" alt="Logo" class="max-w-full max-h-full object-contain p-2">
                @else
                    <span class="text-xs text-slate-400 text-center px-2">No logo</span>
                @endif
            </div>
            <input type="file" name="logo" accept="image/*" class="mt-2 w-full text-xs file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-violet-100 file:text-violet-700">
        </div>
        <div class="flex-1 min-w-[240px] space-y-4">
            <div><label class="text-sm font-medium">Company name</label><input name="company_name" value="{{ old('company_name',$setting->company_name) }}" class="w-full mt-1 rounded-lg border-slate-300"></div>
            <div><label class="text-sm font-medium">Warehouse name</label><input name="warehouse_name" value="{{ old('warehouse_name',$setting->warehouse_name) }}" class="w-full mt-1 rounded-lg border-slate-300" placeholder="Main Warehouse"></div>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="text-sm font-medium">Currency symbol</label><input name="currency" value="{{ old('currency',$setting->currency) }}" class="w-full mt-1 rounded-lg border-slate-300"></div>
        <div><label class="text-sm font-medium">Default tax rate (%)</label><input type="number" step="0.01" name="default_tax_rate" value="{{ old('default_tax_rate',$setting->default_tax_rate ?? 0) }}" class="w-full mt-1 rounded-lg border-slate-300"></div>
    </div>
    <div><label class="text-sm font-medium">Phone</label><input name="phone" value="{{ old('phone',$setting->phone) }}" class="w-full mt-1 rounded-lg border-slate-300"></div>
    <div><label class="text-sm font-medium">Email</label><input type="email" name="email" value="{{ old('email',$setting->email) }}" class="w-full mt-1 rounded-lg border-slate-300"></div>
    <div><label class="text-sm font-medium">Address</label><textarea name="address" rows="3" class="w-full mt-1 rounded-lg border-slate-300">{{ old('address',$setting->address) }}</textarea></div>
    <button class="px-6 py-2.5 bg-violet-600 hover:bg-violet-500 text-white rounded-lg font-medium">Save settings</button>
</form>
@endsection
