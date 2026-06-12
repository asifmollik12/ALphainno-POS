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
}
