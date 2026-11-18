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
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique();
            $table->string('status')->default('pending');
            $table->decimal('sub_total', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('shipping_address');
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
