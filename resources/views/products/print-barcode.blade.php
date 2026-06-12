@extends('layouts.dashboard')

@section('title', 'Print Barcode')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-5">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Print Barcode</h1>
        <p class="text-slate-500 text-sm mt-1">Configure label layout and print product barcodes</p>
    </div>
    <div class="flex gap-2">
        <button type="button" id="btn-preview" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-ai-navy hover:bg-slate-900 text-white text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            Preview
        </button>
        <button type="button" id="btn-print" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-ai-navy hover:bg-slate-900 text-white text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print
        </button>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form id="barcode-form" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 max-w-4xl">
        <div>
            <label class="text-sm font-medium text-slate-700">Page Size</label>
            <select name="page_size" class="w-full mt-1 rounded-lg border border-slate-200 text-sm py-2 px-3">
                <option value="A4" @selected($settings['page_size'] === 'A4')>A4</option>
                <option value="Letter" @selected($settings['page_size'] === 'Letter')>Letter</option>
            </select>
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700">Column</label>
            <input type="number" name="columns" min="1" max="10" value="{{ $settings['columns'] }}" class="w-full mt-1 rounded-lg border border-slate-200 text-sm py-2 px-3">
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700">Row</label>
            <input type="number" name="rows" min="1" max="20" value="{{ $settings['rows'] }}" class="w-full mt-1 rounded-lg border border-slate-200 text-sm py-2 px-3">
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700">Height</label>
            <input type="number" name="height" min="20" max="200" value="{{ $settings['height'] }}" class="w-full mt-1 rounded-lg border border-slate-200 text-sm py-2 px-3">
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700">Width</label>
            <input type="number" name="width" min="0.5" max="5" step="0.1" value="{{ $settings['width'] }}" class="w-full mt-1 rounded-lg border border-slate-200 text-sm py-2 px-3">
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700">FontSize</label>
            <input type="number" name="font_size" min="8" max="32" value="{{ $settings['font_size'] }}" class="w-full mt-1 rounded-lg border border-slate-200 text-sm py-2 px-3">
        </div>
    </form>

    @if ($selectedProducts->isNotEmpty())
        <div class="mt-6 pt-6 border-t border-slate-100">
            <h2 class="text-sm font-semibold text-slate-900 mb-3">Selected products ({{ $selectedProducts->count() }})</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($selectedProducts as $p)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-ai-mist text-xs text-slate-700">{{ $p->name }}</span>
                    <input type="hidden" name="products[]" value="{{ $p->id }}" form="barcode-form">
                @endforeach
            </div>
        </div>
    @else
        <p class="mt-6 pt-6 border-t border-slate-100 text-sm text-slate-500">No product selected — preview will include all products in your catalog.</p>
    @endif
</div>

@push('scripts')
<script>
const previewBase = @json(route('products.print-barcode.preview'));

function openSheet(printNow) {
    const form = document.getElementById('barcode-form');
    const params = new URLSearchParams(new FormData(form));
    if (printNow) params.set('print', '1');
    window.open(`${previewBase}?${params.toString()}`, '_blank');
}

document.getElementById('btn-preview').addEventListener('click', () => openSheet(false));
document.getElementById('btn-print').addEventListener('click', () => openSheet(true));
</script>
@endpush
@endsection
