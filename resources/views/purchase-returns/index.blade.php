@extends('layouts.dashboard')

@section('title', 'Purchase Return')

@section('content')
<div x-data="{ showNotes: true, showSupplier: false, showTotal: false, columnsOpen: false }">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
        <h1 class="text-xl font-bold text-slate-900">Purchase Return</h1>
        <form method="GET" class="flex flex-wrap items-center gap-2 text-sm">
            @foreach (request()->except('from', 'to', 'page') as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <span class="text-slate-400">—</span>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <button type="submit" class="px-3 py-2 rounded-lg bg-ai-mist text-sm font-medium text-slate-700 hover:bg-slate-200">Apply</button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="relative">
                <button type="button" @click="columnsOpen = !columnsOpen" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Columns
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="columnsOpen" x-cloak @click.outside="columnsOpen = false" class="absolute left-0 top-full mt-1 z-20 w-48 rounded-lg border border-slate-200 bg-white shadow-lg py-2 text-sm">
                    <label class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" checked disabled class="rounded border-slate-300"> ID
                    </label>
                    <label class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" checked disabled class="rounded border-slate-300"> Date
                    </label>
                    <label class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" checked disabled class="rounded border-slate-300"> Purchase Invoice ID
                    </label>
                    <label class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" x-model="showNotes" class="rounded border-slate-300"> Notes
                    </label>
                    <label class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" x-model="showSupplier" class="rounded border-slate-300"> Supplier
                    </label>
                    <label class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" x-model="showTotal" class="rounded border-slate-300"> Total
                    </label>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('purchase-returns.create') }}" class="px-3 py-2 rounded-lg bg-ai-navy hover:bg-slate-900 text-white text-sm font-medium">+ New Return</a>
                <a href="{{ route('purchase-returns.print', request()->only(['from','to','q'])) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">Print PDF</a>
                <a href="{{ route('purchase-returns.export', request()->only(['from','to'])) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">Download CSV</a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[720px]">
                <thead class="bg-ai-navy text-white text-left text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Purchase Invoice ID</th>
                        <th class="px-4 py-3" x-show="showNotes">Notes</th>
                        <th class="px-4 py-3" x-show="showSupplier">Supplier</th>
                        <th class="px-4 py-3 text-right" x-show="showTotal">Total</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($returns as $return)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $return->reference }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $return->return_date->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            @if ($return->purchase)
                                <a href="{{ route('purchases.show', $return->purchase) }}" class="text-ai-cyan hover:underline font-medium">{{ $return->purchase->reference }}</a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 max-w-xs truncate" x-show="showNotes">{{ $return->notes ?: '—' }}</td>
                        <td class="px-4 py-3" x-show="showSupplier">{{ $return->supplier->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-medium" x-show="showTotal">{{ number_format($return->total, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('purchase-returns.show', $return) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-ai-navy text-white hover:bg-slate-900" title="View return">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-16 text-center text-slate-400">No purchase returns in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <div>{{ $returns->links() }}</div>
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
</div>
@endsection
