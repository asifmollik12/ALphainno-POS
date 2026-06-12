@extends('layouts.dashboard')

@section('title', 'Edit Supplier')

@section('content')
<div class="max-w-xl mx-auto">
    <a href="{{ route('suppliers.show', $supplier) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-ai-navy text-white mb-4 hover:bg-slate-900" title="Back">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h1 class="text-center text-lg font-bold text-slate-900 mb-6">Edit Supplier Form</h1>

        <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="text-sm font-medium text-slate-700">Name <span class="text-red-500">*</span></label>
                <input name="name" value="{{ old('name', $supplier->name) }}" required class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-ai-purple focus:ring-2 focus:ring-ai-purple/15 outline-none">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Phone <span class="text-red-500">*</span></label>
                <input name="phone" value="{{ old('phone', $supplier->phone) }}" required class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-ai-purple focus:ring-2 focus:ring-ai-purple/15 outline-none">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-ai-purple focus:ring-2 focus:ring-ai-purple/15 outline-none">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Address <span class="text-red-500">*</span></label>
                <textarea name="address" rows="3" required class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-ai-purple focus:ring-2 focus:ring-ai-purple/15 outline-none">{{ old('address', $supplier->address) }}</textarea>
            </div>
            <button type="submit" class="w-full py-3 rounded-lg bg-ai-navy hover:bg-slate-900 text-white font-semibold text-sm">Update Now</button>
        </form>
    </div>
</div>
@endsection
