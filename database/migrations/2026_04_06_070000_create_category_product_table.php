<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('category_product')) {
            Schema::create('category_product', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['category_id', 'product_id']);
            });
        }

        // Migrate existing category_id data from products table into category_product pivot table
        if (Schema::hasColumn('products', 'category_id')) {
            $products = DB::table('products')->whereNotNull('category_id')->get();
            foreach ($products as $product) {
                DB::table('category_product')->insertOrIgnore([
                    'category_id' => $product->category_id,
                    'product_id' => $product->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
