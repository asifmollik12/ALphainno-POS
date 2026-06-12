@php
    $currency = $currency ?? (auth()->user()->shopSetting?->currency ?? '৳');
    $payUrlPattern = route('purchases.pay', ['purchase' => '__ID__']);
@endphp

<div x-data="payModal()" @open-pay.window="openPay($event.detail)" x-cloak>
    <div x-show="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" @keydown.escape.window="show = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md" @click.outside="show = false">
            <div class="flex items-center justify-between px-5 py-4 border-b">
                <h3 class="font-bold text-slate-900">Make Purchase Payment</h3>
                <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
            </div>
            <form :action="actionUrl" method="POST" class="p-5 space-y-4">
                @csrf
                <p class="text-sm">Due Amount : <span class="font-bold text-red-600" x-text="dueText"></span></p>
                <div>
                    <label class="text-sm font-medium text-slate-700">Date</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-sm font-medium text-slate-700">Paid Amount</label>
                        <button type="button" @click="setFullPaid()" class="text-xs px-2 py-0.5 rounded bg-ai-sky text-ai-cyan font-medium">Full Paid</button>
                    </div>
                    <input type="number" step="0.01" name="paid_amount" x-model="paid" required min="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Payment Method</label>
                    <select name="payment_method" class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="mobile">Mobile Banking</option>
                        <option value="bank">Bank Transfer</option>
                    </select>
                </div>
                <button type="submit" class="w-full py-2.5 rounded-lg bg-ai-navy hover:bg-slate-900 text-white font-medium text-sm">Pay Now</button>
            </form>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
function payModal() {
    const payUrlPattern = @json($payUrlPattern);
    return {
        show: false,
        purchaseId: null,
        due: 0,
        paid: 0,
        currency: @json($currency),
        get actionUrl() { return payUrlPattern.replace('__ID__', this.purchaseId); },
        get dueText() { return this.currency + Number(this.due).toFixed(2); },
        openPay(detail) {
            this.purchaseId = detail.id;
            this.due = detail.due;
            this.paid = detail.due;
            this.show = true;
        },
        setFullPaid() { this.paid = this.due; },
    };
}
</script>
@endpush
@endonce
