<?php

namespace App\Livewire;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public $setting;

    public $items = [];
    public $availableCoupons = [];
    public $voucherCode = '';
    public $appliedVoucher = null;
    public $appliedCouponData = null;
    public $voucherDiscount = 0;
    public $voucherError = '';
    public $toastMessage = '';
    public $showToast = false;

    public function mount()
    {
        $this->setting = Setting::getSystemSetting();
        $this->items = CartService::syncWithDatabase();
        $this->availableCoupons = Coupon::where('is_active', true)->orderBy('sort_order', 'asc')->get();

        $savedVoucher = CartService::getVoucher();
        if ($savedVoucher) {
            $this->applyVoucher($savedVoucher, false);
        }
    }

    public function incrementItem($index)
    {
        CartService::increment($index);
        $this->items = CartService::getItems();
        $this->dispatch('cart-updated');
        $this->recalcVoucher();
    }

    public function decrementItem($index)
    {
        CartService::decrement($index);
        $this->items = CartService::getItems();
        $this->dispatch('cart-updated');
        $this->recalcVoucher();
    }

    public function removeItem($index)
    {
        CartService::remove($index);
        $this->items = CartService::getItems();
        $this->dispatch('cart-updated');
        $this->recalcVoucher();
        $this->triggerToast("Đã xóa sản phẩm khỏi giỏ hàng");
    }

    public function changeItemVariant($index, $variantId)
    {
        if (!$variantId) {
            return;
        }
        CartService::changeVariant((int) $index, (int) $variantId);
        $this->items = CartService::getItems();
        $this->dispatch('cart-updated');
        $this->recalcVoucher();
        $this->triggerToast("Đã cập nhật phân loại sản phẩm");
    }

    public function getProductVariantsMapProperty(): array
    {
        $productIds = collect($this->items)->pluck('id')->filter()->unique()->values()->all();
        if (empty($productIds)) {
            return [];
        }

        return Product::with(['variants' => fn ($q) => $q->orderBy('sort_order')->with('attributeValues')])
            ->whereIn('id', $productIds)
            ->get()
            ->mapWithKeys(function ($prod) {
                return [
                    $prod->id => $prod->variants->map(fn ($v) => [
                        'id' => $v->id,
                        'label' => $v->label,
                        'price' => $v->effective_price,
                    ])->values()->all(),
                ];
            })
            ->all();
    }

    public function clearCart()
    {
        CartService::clear();
        $this->items = [];
        $this->removeVoucher();
        $this->dispatch('cart-updated');
        $this->triggerToast("Đã làm trống giỏ hàng");
    }

    public function applyVoucher($code = null, $showToast = true)
    {
        $this->voucherError = '';
        $inputCode = strtoupper(trim((string) ($code ?? $this->voucherCode)));

        if (empty($inputCode)) {
            $this->voucherError = 'Vui lòng nhập mã giảm giá.';
            return;
        }

        $coupon = Coupon::where('code', $inputCode)->first();
        if (!$coupon) {
            $this->voucherError = 'Mã giảm giá không tồn tại hoặc đã hết hạn.';
            return;
        }

        $subtotal = $this->getSubtotalProperty();
        $errorMsg = null;
        if (!$coupon->isValidFor($subtotal, $errorMsg)) {
            $this->voucherError = $errorMsg ?: 'Mã giảm giá không hợp lệ.';
            return;
        }

        $isShippingCoupon = in_array($coupon->type, ['shipping', 'free_shipping'])
            || in_array($coupon->code, ['FREESHIP', 'FREESHIP25', 'FREESHIP40', 'SHIP25'])
            || str_starts_with($coupon->code, 'GIAMSHIP')
            || str_contains(strtoupper($coupon->code), 'FREESHIP');

        $discount = 0;
        if ($isShippingCoupon) {
            $discount = 0; // Giảm vào phí vận chuyển ở bước thanh toán
        } elseif (in_array($coupon->type, ['percentage', 'percent'])) {
            $discount = round(($subtotal * (float) $coupon->value) / 100);
            if ($coupon->max_discount && (float) $coupon->max_discount > 0 && $discount > (float) $coupon->max_discount) {
                $discount = (float) $coupon->max_discount;
            }
        } elseif ($coupon->type === 'fixed') {
            $discount = (float) $coupon->value;
            if ($coupon->max_discount && (float) $coupon->max_discount > 0 && $discount > (float) $coupon->max_discount) {
                $discount = (float) $coupon->max_discount;
            }
        } elseif ($coupon->type === 'gift') {
            $discount = 0;
        }

        $this->appliedVoucher = $coupon->code;
        $this->appliedCouponData = [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'title' => $coupon->title,
            'badge' => $coupon->badge,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'description' => $coupon->description,
            'image_url' => $coupon->image_url,
        ];
        $this->voucherDiscount = min($subtotal, $discount);
        $this->voucherCode = '';
        CartService::setVoucher($coupon->code);

        if ($showToast) {
            $msg = 'Đã áp dụng mã: ' . ($coupon->title ?: $coupon->code);
            $this->triggerToast($msg);
        }
    }

    public function removeVoucher()
    {
        $this->appliedVoucher = null;
        $this->appliedCouponData = null;
        $this->voucherDiscount = 0;
        $this->voucherError = '';
        CartService::removeVoucher();
        $this->triggerToast("Đã hủy áp dụng mã giảm giá");
    }

    protected function recalcVoucher()
    {
        if ($this->appliedVoucher) {
            $coupon = Coupon::where('code', $this->appliedVoucher)->where('is_active', true)->first();
            if ($coupon) {
                $subtotal = $this->getSubtotalProperty();
                if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
                    $this->removeVoucher();
                    $this->triggerToast("Đơn hàng không còn đủ điều kiện áp dụng mã " . $coupon->code);
                    return;
                }
                if (in_array($coupon->type, ['percentage', 'percent'])) {
                    $discount = round(($subtotal * $coupon->value) / 100);
                    if ($coupon->max_discount && $discount > $coupon->max_discount) {
                        $discount = (float) $coupon->max_discount;
                    }
                    $this->voucherDiscount = min($subtotal, $discount);
                }
            }
        }
    }

    public function triggerToast($msg)
    {
        $this->toastMessage = $msg;
        $this->showToast = true;
    }

    public function getSubtotalProperty()
    {
        $sum = 0;
        foreach ($this->items as $item) {
            $sum += ($item['price'] * $item['qty']);
        }
        return $sum;
    }

    public function getShippingFeeProperty()
    {
        return CartService::calculateShippingFee($this->getSubtotalProperty(), $this->appliedVoucher);
    }

    public function getTotalProperty()
    {
        $total = $this->getSubtotalProperty() - $this->voucherDiscount + $this->getShippingFeeProperty();
        return max(0, $total);
    }
};
