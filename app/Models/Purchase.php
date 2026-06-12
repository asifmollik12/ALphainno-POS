<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'user_id', 'supplier_id', 'reference', 'total', 'tax_amount', 'paid_amount', 'due_amount',
        'returned_amount', 'payment_status', 'purchase_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
            'returned_amount' => 'decimal:2',
            'purchase_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'related_id')
            ->where('related_type', self::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function statusLabel(): string
    {
        return match ($this->payment_status) {
            'paid' => 'PAID',
            'partial' => 'PARTIAL',
            default => 'UNPAID',
        };
    }

    public function gmailComposeUrl(?string $email = null): ?string
    {
        $email = $email ?? $this->supplier?->email;
        if (! $email) {
            return null;
        }

        $subject = $this->emailSubject();
        $body = $this->emailDraftBody();

        return 'https://mail.google.com/mail/?view=cm&fs=1'
            .'&to='.rawurlencode($email)
            .'&su='.rawurlencode($subject)
            .'&body='.rawurlencode($body);
    }

    public function emailSubject(): string
    {
        $setting = auth()->user()?->shopSetting;
        $company = $setting?->company_name ?? 'Alphainno POS';
        $currency = $setting?->currency ?? '৳';
        $due = $currency.number_format((float) $this->due_amount, 2);

        return "Purchase Invoice #{$this->reference} — Due {$due} | {$company}";
    }

    public function emailDraftBody(): string
    {
        $this->loadMissing(['items', 'supplier']);

        $setting = auth()->user()?->shopSetting;
        $user = auth()->user();
        $currency = $setting?->currency ?? '৳';
        $company = $setting?->company_name ?? 'Alphainno POS';
        $fmt = fn (float $n) => $currency.number_format($n, 2);

        $supplierName = $this->supplier?->name ?? 'Supplier';
        $lineSubtotal = 0;
        $lineTax = 0;
        $itemLines = [];

        foreach ($this->items as $index => $item) {
            $qty = (int) $item->quantity;
            $unit = (float) $item->unit_cost;
            $tax = (float) ($item->tax_amount ?? 0);
            $lineBase = round($qty * $unit, 2);
            $lineSubtotal += $lineBase;
            $lineTax += $tax;
            $lineTotal = round($lineBase + $tax, 2);
            $taxRate = (float) ($item->tax_rate ?? 0);

            $itemLines[] = sprintf(
                '%d. %s'."\n".'   Quantity: %d  |  Unit Price: %s  |  Tax: %s (%s%%)  |  Line Total: %s',
                $index + 1,
                $item->product_name,
                $qty,
                $fmt($unit),
                $fmt($tax),
                number_format($taxRate, 1),
                $fmt($lineTotal)
            );
        }

        if ($itemLines === []) {
            $itemLines[] = 'No line items recorded.';
        }

        $taxTotal = (float) ($this->tax_amount ?? $lineTax);
        $subtotalExclTax = $lineSubtotal > 0 ? $lineSubtotal : max((float) $this->total - $taxTotal, 0);
        $showUrl = route('purchases.show', $this);
        $divider = str_repeat('─', 42);

        $lines = [
            'Dear '.$supplierName.',',
            '',
            'We hope this message finds you well.',
            '',
            'Please review the purchase invoice details below. Kindly confirm the order and advise us on payment terms if any amount remains outstanding.',
            '',
            $divider,
            'PURCHASE INVOICE',
            $divider,
            '',
            'Invoice Number:  #'.$this->reference,
            'Invoice Date:    '.$this->purchase_date->format('F d, Y'),
            'Payment Status:  '.$this->statusLabel(),
            '',
            'Supplier Name:   '.($this->supplier?->name ?? '—'),
            'Supplier Phone:  '.($this->supplier?->phone ?? '—'),
            'Supplier Email:  '.($this->supplier?->email ?? '—'),
            '',
            $divider,
            'ITEM DETAILS',
            $divider,
            '',
            implode("\n\n", $itemLines),
            '',
            $divider,
            'PAYMENT SUMMARY',
            $divider,
            '',
            'Subtotal (excl. tax):  '.$fmt($subtotalExclTax),
            'Tax Amount:            '.$fmt($taxTotal),
            'Total Amount:          '.$fmt((float) $this->total),
            'Paid Amount:           '.$fmt((float) $this->paid_amount),
            'Due Amount:            '.$fmt((float) $this->due_amount),
            '',
        ];

        if ($this->notes) {
            $lines[] = 'Note: '.$this->notes;
            $lines[] = '';
        }

        $lines = array_merge($lines, [
            'View full invoice online:',
            $showUrl,
            '',
            'If you have any questions about this invoice, please contact us at your earliest convenience.',
            '',
            'Thank you for your continued partnership.',
            '',
            'Best regards,',
            $user?->name ?? 'Accounts Team',
            $company,
        ]);

        if ($setting?->phone) {
            $lines[] = 'Phone: '.$setting->phone;
        }
        if ($setting?->email) {
            $lines[] = 'Email: '.$setting->email;
        }
        if ($setting?->address) {
            $lines[] = 'Address: '.$setting->address;
        }

        return implode("\n", $lines);
    }
}
