<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'brand_id')) {
                $table->foreignId('brand_id')->nullable()->after('category_id')->constrained('brands')->nullOnDelete();
            }
            if (!Schema::hasColumn('products', 'unit')) {
                $table->string('unit')->nullable()->after('sku');
            }
            if (!Schema::hasColumn('products', 'weight')) {
                $table->string('weight')->nullable()->after('unit');
            }
            if (!Schema::hasColumn('products', 'badge')) {
                $table->string('badge')->nullable()->after('image_url');
            }
            if (!Schema::hasColumn('products', 'badge_color')) {
                $table->string('badge_color')->nullable()->after('badge');
            }
            if (!Schema::hasColumn('products', 'discount_label')) {
                $table->string('discount_label')->nullable()->after('badge_color');
            }
            if (!Schema::hasColumn('products', 'is_flash_sale')) {
                $table->boolean('is_flash_sale')->default(false)->after('is_featured');
            }
            if (!Schema::hasColumn('products', 'sold_count')) {
                $table->integer('sold_count')->default(0)->after('view_count');
            }
            if (!Schema::hasColumn('products', 'stock_quantity')) {
                $table->integer('stock_quantity')->default(100)->after('sold_count');
            }
            if (!Schema::hasColumn('products', 'stock_warning')) {
                $table->string('stock_warning')->nullable()->after('stock_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = [
                'brand_id', 'unit', 'weight', 'badge', 'badge_color',
                'discount_label', 'is_flash_sale', 'sold_count', 'stock_quantity', 'stock_warning'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    if ($column === 'brand_id') {
                        $table->dropForeign(['brand_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
