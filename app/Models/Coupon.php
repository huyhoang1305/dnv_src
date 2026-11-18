<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'title', 'badge', 'icon', 'color', 'description', 'image_url',
        'type', 'value', 'min_order_amount', 'max_discount',
        'usage_limit', 'used_count', 'valid_from', 'valid_until',
        'is_active', 'sort_order'
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function getThumbnailUrlAttribute(): ?string
    {
        if (empty($this->image_url)) {
            return null;
        }

        return curator_url($this->image_url) ?: (filter_var($this->image_url, FILTER_VALIDATE_URL) ? $this->image_url : asset(ltrim($this->image_url, '/')));
    }

    public function isValidFor(float $subtotal, ?string &$error = null): bool
    {
        if (!$this->is_active) {
            $error = 'Mã giảm giá hiện đang tạm ngưng áp dụng.';
            return false;
        }

        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) {
            $error = 'Mã giảm giá chưa đến thời gian áp dụng.';
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            $error = 'Mã giảm giá đã hết hạn sử dụng.';
            return false;
        }

        if ($this->usage_limit > 0 && $this->used_count >= $this->usage_limit) {
            $error = 'Mã giảm giá đã hết lượt sử dụng.';
            return false;
        }

        if ($this->min_order_amount && $subtotal < (float) $this->min_order_amount) {
            $error = 'Đơn hàng tối thiểu ' . number_format($this->min_order_amount, 0, ',', '.') . 'đ để áp dụng mã này.';
            return false;
        }

        return true;
    }
}
