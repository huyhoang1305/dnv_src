<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'description', 'content',
        'base_price', 'sale_price', 'sku', 'unit', 'weight', 'image_url', 'badge',
        'badge_color', 'discount_label', 'is_active', 'is_featured', 'is_flash_sale',
        'sold_count', 'stock_quantity', 'stock_warning', 'view_count', 'sort_order',
        'seo_title', 'seo_description', 'seo_keywords', 'gallery',
    ];

    protected $casts = [
        'gallery' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_flash_sale' => 'boolean',
        'base_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    protected $attributes = [
        'sort_order' => 0,
        'is_active' => true,
        'is_featured' => false,
        'is_flash_sale' => false,
        'sold_count' => 0,
        'stock_quantity' => 100,
    ];

    public function scopeSellable($query)
    {
        return $query->where('is_active', true)->where('base_price', '>', 0);
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if ($product->base_price === null || (float) $product->base_price <= 0) {
                $product->base_price = max(0, (float) ($product->base_price ?? 0));
                $product->is_active = false;
            }
            if ($product->sale_price !== null && (float) $product->sale_price <= 0) {
                $product->sale_price = null;
            }
            if (is_string($product->sku) && trim($product->sku) === '') {
                $product->sku = null;
            }
        });
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
