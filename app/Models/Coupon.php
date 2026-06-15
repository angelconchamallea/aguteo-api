<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_purchase_amount', 'usage_limit',
        'usage_limit_per_customer', 'times_used', 'starts_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'value' => 'integer',
        'min_purchase_amount' => 'integer',
        'usage_limit' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'times_used' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_category');
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'coupon_brand');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
