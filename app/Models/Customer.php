<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'user_id', 'name', 'email', 'phone', 'address', 'balance_due',
        'contact_person', 'mobile', 'website', 'tax_number',
        'billing_country', 'billing_city',
        'shipping_address', 'shipping_country', 'shipping_city',
    ];

    protected function casts(): array
    {
        return ['balance_due' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
