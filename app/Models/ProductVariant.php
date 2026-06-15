<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id', 'size', 'color', 'sku', 'stock', 'price_override',
    ];

    protected $casts = [
        'stock' => 'integer',
        'price_override' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getEffectivePriceAttribute(): int
    {
        return $this->price_override ?? $this->product->price;
    }
}
