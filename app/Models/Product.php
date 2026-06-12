<?php

namespace App\Models;

use App\Support\Uploads;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'sku',
        'category',
        'brand',
        'unit',
        'barcode',
        'image_path',
        'price',
        'discount_rate',
        'tax_rate',
        'cost_price',
        'stock',
        'min_stock',
        'uom_value',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_rate' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'uom_value' => 'decimal:2',
            'stock' => 'integer',
            'min_stock' => 'integer',
        ];
    }

    public function isShortage(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function imageUrl(): ?string
    {
        return Uploads::url($this->image_path);
    }
}
