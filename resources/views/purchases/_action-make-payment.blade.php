@props(['purchase'])

@php $hasDue = (float) $purchase->due_amount > 0; @endphp

<button type="button"
        data-pay-id="{{ $purchase->id }}"
        data-pay-ref="{{ $purchase->reference }}"
        data-pay-due="{{ $purchase->due_amount }}"
        @if ($hasDue)
            @click="openPayModalFromButton($event.currentTarget)"
        @endif
        @disabled(! $hasDue)
        class="w-full flex items-center gap-2 px-4 py-2 text-left text-sm {{ $hasDue ? 'hover:bg-slate-50 text-slate-700' : 'opacity-40 cursor-not-allowed text-slate-400' }}"
        @if (! $hasDue) title="No due amount on this invoice" @endif>
    <svg class="w-4 h-4 {{ $hasDue ? '' : 'text-slate-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    Make a payment
</button>
