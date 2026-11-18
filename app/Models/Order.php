<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_number', 'status', 'sub_total', 
        'discount_amount', 'coupon_id', 'shipping_fee', 'total_amount',
        'customer_name', 'customer_phone', 'customer_email', 
        'shipping_address', 'order_notes', 'delivery_method',
        'payment_method', 'payment_status', 'sort_order'
    ];

    public static function generateOrderNumber(): string
    {
        do {
            $code = 'DNV-' . date('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
        } while (rescue(fn () => self::where('order_number', $code)->exists(), false, false));

        return $code;
    }

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });

        static::updated(function (self $order) {
            if ($order->wasChanged('status') && $order->status === 'cancelled' && $order->getOriginal('status') !== 'cancelled') {
                $order->loadMissing('items');
                foreach ($order->items as $item) {
                    $qty = max(1, (int) ($item->quantity ?? 1));
                    if ($item->product_id && ($prod = Product::find($item->product_id))) {
                        $prod->stock_quantity = (int) ($prod->stock_quantity ?? 0) + $qty;
                        $prod->sold_count = max(0, (int) ($prod->sold_count ?? 0) - $qty);
                        $prod->saveQuietly();
                    }
                    if ($item->product_variant_id && ($variant = ProductVariant::find($item->product_variant_id))) {
                        $variant->stock = (int) ($variant->stock ?? 0) + $qty;
                        $variant->saveQuietly();
                    }
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
