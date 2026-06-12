<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopSetting extends Model
{
    protected $fillable = [
        'user_id', 'company_name', 'warehouse_name', 'currency', 'default_tax_rate',
        'phone', 'email', 'address',
    ];

    protected function casts(): array
    {
        return ['default_tax_rate' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
