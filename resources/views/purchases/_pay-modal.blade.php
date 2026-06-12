@php
    $currency = $currency ?? (auth()->user()->shopSetting?->currency ?? '৳');
    $payUrlPattern = route('purchases.pay', ['purchase' => '__ID__']);
@endphp

<div x-data="payModal()" @open-pay.window="openPay($event.detail)" x-cloak>
    <div x-show="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" @keydown.escape.window="show = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg" @click.outside="show = false">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900">Make Purchase</h3>
                <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
            </div>
            <form :action="actionUrl" method="POST" class="p-5 space-y-4" @submit="syncPaidAmount">
                @csrf
                <p class="text-center text-sm">Due Amount : <span class="font-bold text-red-600 text-base" x-text="dueText"></span></p>

                <div>
                    <label class="text-sm font-medium text-slate-700">Date</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-slate-700">Paid Amount</label>
                        <button type="button" @click="setFullPaid()" class="text-xs px-2 py-0.5 rounded bg-ai-sky text-ai-cyan font-semibold">Full Paid</button>
                    </div>
                    <template x-for="(row, index) in payments" :key="index">
                        <div class="flex items-center gap-2 mb-2">
                            <input type="number" step="0.01" x-model.number="row.amount" min="0" required
                                   class="flex-1 rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                            <select x-model="row.method" class="w-36 rounded-lg border border-slate-200 px-2 py-2.5 text-sm text-slate-600">
                                <option value="">Select Pay...</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="mobile">Mobile Banking</option>
                                <option value="bank">Bank Transfer</option>
                            </select>
                            <button type="button" @click="removePayment(index)" x-show="payments.length > 1"
                                    class="p-2 text-slate-400 hover:text-red-500">&times;</button>
                        </div>
                    </template>
                    <input type="hidden" name="paid_amount" :value="totalPaid">
                    <input type="hidden" name="payment_method" :value="primaryMethod">
                    <button type="button" @click="addPayment()" class="w-full mt-1 py-2 rounded-lg border border-dashed border-slate-300 text-sm text-slate-600 hover:bg-slate-50 inline-flex items-center justify-center gap-1">
                        <span class="text-lg leading-none">+</span> Add Payment
                    </button>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Purchase Invoice No</label>
                    <input type="text" name="payment_reference" x-model="invoiceRef" class="w-full mt-1 rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                </div>

                <button type="submit" :disabled="due <= 0" class="w-full py-3 rounded-lg bg-ai-navy hover:bg-slate-900 text-white font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed">Pay Now</button>
                <p x-show="due <= 0" class="text-center text-xs text-slate-500">This invoice is already fully paid.</p>
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
        invoiceRef: '',
        payments: [{ amount: 0, method: '' }],
        currency: @json($currency),
        get actionUrl() { return payUrlPattern.replace('__ID__', this.purchaseId); },
        get dueText() { return this.currency + Number(this.due).toFixed(2); },
        get totalPaid() {
            return this.payments.reduce((sum, row) => sum + (Number(row.amount) || 0), 0);
        },
        get primaryMethod() {
            const row = this.payments.find(r => r.method) || this.payments[0];
            return row?.method || 'cash';
        },
        openPay(detail) {
            this.purchaseId = detail.id;
            this.due = detail.due;
            this.invoiceRef = detail.ref || '';
            this.payments = [{ amount: detail.due, method: '' }];
            this.show = true;
        },
        setFullPaid() {
            if (this.payments.length === 1) {
                this.payments[0].amount = this.due;
            } else {
                this.payments = [{ amount: this.due, method: this.payments[0]?.method || '' }];
            }
        },
        addPayment() {
            this.payments.push({ amount: 0, method: '' });
        },
        removePayment(index) {
            if (this.payments.length > 1) {
                this.payments.splice(index, 1);
            }
        },
        syncPaidAmount() {
            if (this.totalPaid <= 0) {
                alert('Enter a paid amount.');
                return false;
            }
            if (this.totalPaid > this.due) {
                this.payments[0].amount = Math.max(this.due - this.payments.slice(1).reduce((s, r) => s + (Number(r.amount) || 0), 0), 0);
            }
        },
    };
}
</script>
@endpush
@endonce
