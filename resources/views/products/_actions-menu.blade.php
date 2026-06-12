@props(['product', 'from' => null])

@php
    $viewParams = $from ? ['product' => $product, 'from' => $from] : ['product' => $product];
@endphp

<x-row-actions-dropdown>
    <a href="{{ route('products.show', $viewParams) }}" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-slate-50 text-slate-700">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        View
    </a>
    <a href="{{ route('products.edit', $product) }}" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-slate-50 text-slate-700">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
    </a>
    <a href="{{ route('products.print-barcode', ['product' => $product->id]) }}" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-slate-50 text-slate-700">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h1v10H4V7zm3 0h1v10H7V7zm2 0h2v10H9V7zm3 0h1v10h-1V7zm2 0h3v10h-3V7zm4 0h1v10h-1V7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h18v14H3V5z"/></svg>
        Barcode
    </a>
</x-row-actions-dropdown>
