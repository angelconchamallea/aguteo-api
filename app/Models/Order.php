<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_number', 'customer_id', 'status',
        'subtotal', 'discount_total', 'shipping_total', 'total',
        'coupon_id', 'coupon_code', 'payment_method',
        'webpay_token', 'webpay_buy_order', 'webpay_authorization_code', 'webpay_card_last4',
        'shipping_address', 'shipping_rate_name',
        'paid_at', 'shipped_at', 'delivered_at', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'discount_total' => 'integer',
        'shipping_total' => 'integer',
        'total' => 'integer',
        'shipping_address' => 'array',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function markAsPaid(string $authorizationCode, string $cardLast4): void
    {
        $this->update([
            'status' => 'paid',
            'webpay_authorization_code' => $authorizationCode,
            'webpay_card_last4' => $cardLast4,
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    public function markAsShipped(?string $tracking = null): void
    {
        $this->update([
            'status' => 'shipped',
            'shipped_at' => now(),
            'notes' => $tracking ? "Tracking: {$tracking}" : $this->notes,
        ]);
    }
}
