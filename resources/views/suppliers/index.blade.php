@extends('layouts.dashboard')

@section('title', 'Supplier List')

@section('content')
@php $openCreate = request('create') === '1'; @endphp

<div class="flex flex-wrap items-center justify-between gap-4 mb-5">
    <h1 class="text-xl font-bold text-slate-900">Supplier List</h1>
    <button type="button" @click="showCreate = true" class="px-4 py-2 rounded-lg bg-ai-navy hover:bg-slate-900 text-white text-sm font-medium">+ Create Supplier</button>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4 p-4">
    <form method="GET" class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2 flex-1 min-w-[200px]">
            <div class="relative flex-1 max-w-xs">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search..." class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-200 text-sm">
            </div>
            <button type="submit" class="px-3 py-2 rounded-lg bg-ai-mist text-sm font-medium text-slate-700 hover:bg-slate-200">Search</button>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('suppliers.print') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">Print PDF</a>
            <a href="{{ route('suppliers.export') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">Download CSV</a>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[720px]">
            <thead class="bg-ai-navy text-white text-left text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Address</th>
                    <th class="px-4 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($suppliers as $supplier)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-slate-500">{{ $supplier->id }}</td>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $supplier->name }}</td>
                    <td class="px-4 py-3">{{ $supplier->phone ?: '—' }}</td>
                    <td class="px-4 py-3 text-slate-600 max-w-xs truncate">{{ $supplier->address ?: '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('suppliers.show', $supplier) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-ai-navy text-white hover:bg-slate-900" title="View supplier">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-16 text-center text-slate-400">No suppliers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 flex flex-wrap items-center justify-between gap-3">
    <div>{{ $suppliers->links() }}</div>
    <form method="GET" class="flex items-center gap-2 text-sm">
        @foreach (request()->except('per_page', 'page') as $key => $val)
            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
        @endforeach
        <select name="per_page" onchange="this.form.submit()" class="rounded-lg border border-slate-200 py-1.5 px-2 text-sm">
            @foreach ([10, 25, 50] as $n)
                <option value="{{ $n }}" @selected(request('per_page', 10) == $n)>{{ $n }}/Page</option>
            @endforeach
        </select>
    </form>
</div>

{{-- Create Supplier Modal --}}
<div x-data="{ showCreate: @json($openCreate) }" x-cloak>
    <div x-show="showCreate" class="fixed inset-0 z-[250] flex items-center justify-center p-4 bg-black/40" @keydown.escape.window="showCreate = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.outside="showCreate = false">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 sticky top-0 bg-white">
                <h3 class="font-bold text-slate-900">Create Supplier</h3>
                <button type="button" @click="showCreate = false" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
            </div>

            <div class="p-5">
                <h4 class="text-sm font-semibold text-slate-800 mb-3">Add Supplier</h4>
                <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-sm font-medium text-slate-700">Name <span class="text-red-500">*</span></label>
                        <input name="name" value="{{ old('name') }}" required class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Phone <span class="text-red-500">*</span></label>
                        <input name="phone" value="{{ old('phone') }}" required class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Address</label>
                        <input name="address" value="{{ old('address') }}" class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded-lg bg-ai-navy hover:bg-slate-900 text-white font-medium text-sm">Create Supplier</button>
                </form>

                <div class="mt-6 pt-6 border-t border-slate-100">
                    <h4 class="text-sm font-semibold text-slate-800 mb-2">Import From CSV</h4>
                    <p class="text-xs text-red-500 mb-3">Please select a CSV file for uploading</p>
                    <a href="{{ route('suppliers.demo-csv') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-ai-navy text-white text-xs font-medium mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Demo CSV
                    </a>
                    <form method="POST" action="{{ route('suppliers.import') }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="file" name="csv_file" accept=".csv,.txt" required class="w-full text-sm file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700">
                        <button type="submit" class="w-full py-2.5 rounded-lg bg-slate-400 hover:bg-slate-500 text-white font-medium text-sm">Import From CSV</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
