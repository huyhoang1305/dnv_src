<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_notes')) {
                $table->text('order_notes')->nullable()->after('shipping_address');
            }
            if (!Schema::hasColumn('orders', 'delivery_method')) {
                $table->string('delivery_method')->default('express_1h')->after('order_notes');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'product_name')) {
                $table->string('product_name')->nullable()->after('product_variant_id');
            }
            if (!Schema::hasColumn('order_items', 'product_image')) {
                $table->string('product_image')->nullable()->after('product_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'order_notes')) {
                $table->dropColumn('order_notes');
            }
            if (Schema::hasColumn('orders', 'delivery_method')) {
                $table->dropColumn('delivery_method');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_name')) {
                $table->dropColumn('product_name');
            }
            if (Schema::hasColumn('order_items', 'product_image')) {
                $table->dropColumn('product_image');
            }
        });
    }
};
