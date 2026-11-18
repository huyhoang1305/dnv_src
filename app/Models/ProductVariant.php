<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'sku', 'price', 'stock', 'image_url', 'sort_order'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $variant): void {
            if (is_string($variant->sku) && trim($variant->sku) === '') {
                $variant->sku = null;
            }
            if ($variant->price !== null && (float) $variant->price <= 0) {
                $variant->price = null;
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attribute');
    }

    public function getLabelAttribute(): string
    {
        $vals = $this->relationLoaded('attributeValues')
            ? $this->attributeValues
            : $this->attributeValues()->with('attributeType')->get();

        if ($vals->isNotEmpty()) {
            return $vals->pluck('value')->filter()->join(' - ');
        }

        return $this->sku ?: ('Phân loại #' . $this->id);
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->price !== null && (float) $this->price > 0) {
            return (float) $this->price;
        }

        $prod = $this->product;
        if (!$prod) {
            return 0;
        }

        return (float) (($prod->sale_price && $prod->sale_price > 0) ? $prod->sale_price : $prod->base_price);
    }
}
