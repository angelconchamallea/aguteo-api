<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['pending', 'paid', 'preparing', 'shipped', 'delivered', 'cancelled', 'failed'])
                  ->default('pending');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('discount_total')->default(0);
            $table->unsignedInteger('shipping_total');
            $table->unsignedInteger('total');
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code')->nullable();
            $table->string('payment_method')->default('webpay');
            $table->string('webpay_token')->nullable();
            $table->string('webpay_buy_order')->nullable();
            $table->string('webpay_authorization_code')->nullable();
            $table->string('webpay_card_last4', 4)->nullable();
            $table->jsonb('shipping_address');
            $table->string('shipping_rate_name');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
