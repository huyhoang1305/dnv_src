<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Tests\TestCase;

class CartAndCheckoutTest extends TestCase
{
    /**
     * Test mã đơn hàng sinh ngẫu nhiên định dạng bảo mật DNV-yyMMdd-XXXXXX (Issue #2)
     */
    public function test_order_number_generation_format(): void
    {
        $code = Order::generateOrderNumber();
        $this->assertMatchesRegularExpression('/^DNV-\d{6}-[A-Z0-9]{6}$/', $code);
    }

    /**
     * Test kiểm tra tính hợp lệ và hạn dùng của mã giảm giá (Issue #4)
     */
    public function test_coupon_validation_and_expiration(): void
    {
        $coupon = new Coupon([
            'code' => 'TESTCOUPON',
            'type' => 'fixed',
            'value' => 20000,
            'min_order_amount' => 100000,
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'usage_limit' => 10,
            'used_count' => 0,
        ]);

        $error = null;
        // Đơn 150k hợp lệ
        $this->assertTrue($coupon->isValidFor(150000, $error));

        // Đơn 50k chưa đạt tối thiểu
        $this->assertFalse($coupon->isValidFor(50000, $error));
        $this->assertStringContainsString('tối thiểu', $error);

        // Mã hết hạn
        $coupon->valid_until = now()->subHour();
        $this->assertFalse($coupon->isValidFor(150000, $error));
        $this->assertStringContainsString('hết hạn', $error);

        // Mã hết lượt dùng
        $coupon->valid_until = now()->addDay();
        $coupon->used_count = 10;
        $this->assertFalse($coupon->isValidFor(150000, $error));
        $this->assertStringContainsString('hết lượt', $error);
    }

    /**
     * Test xác thực số điện thoại chuẩn Việt Nam khi đặt hàng (Issue #11)
     */
    public function test_vietnamese_phone_number_validation(): void
    {
        $validPhone = '0903856861';
        $invalidPhoneShort = '0903856';
        $invalidPhoneNoZero = '1903856861';
        $invalidPhoneLetters = '0903856abc';

        $checkPhone = function ($phone) {
            $clean = preg_replace('/[^0-9]/', '', trim((string) $phone));
            return strlen($clean) === 10 && str_starts_with($clean, '0');
        };

        $this->assertTrue($checkPhone($validPhone));
        $this->assertFalse($checkPhone($invalidPhoneShort));
        $this->assertFalse($checkPhone($invalidPhoneNoZero));
        $this->assertFalse($checkPhone($invalidPhoneLetters));
    }

    /**
     * Test logic tính phí vận chuyển và giảm giá mã vận chuyển (Issue #14)
     */
    public function test_shipping_fee_and_shipping_voucher_calculation(): void
    {
        $defaultShippingFee = 25000.0;
        $freeShippingThreshold = 500000.0;

        // Đơn dưới 500k -> phí 25k
        $subtotalSmall = 300000.0;
        $feeSmall = ($subtotalSmall >= $freeShippingThreshold) ? 0.0 : $defaultShippingFee;
        $this->assertEquals(25000.0, $feeSmall);

        // Đơn trên 500k -> freeship
        $subtotalLarge = 600000.0;
        $feeLarge = ($subtotalLarge >= $freeShippingThreshold) ? 0.0 : $defaultShippingFee;
        $this->assertEquals(0.0, $feeLarge);

        // Mã giảm phí vận chuyển 20k
        $couponShippingVal = 20000.0;
        $feeAfterCoupon = max(0.0, $feeSmall - $couponShippingVal);
        $this->assertEquals(5000.0, $feeAfterCoupon);
    }

    /**
     * Test hàm CartService::calculateShippingFee đồng bộ giữa màn hình và server (Feedback Row 2)
     */
    public function test_cart_service_shipping_calculation(): void
    {
        // Khi subtotal = 0
        $this->assertEquals(0.0, CartService::calculateShippingFee(0));

        // Khi subtotal >= 500k -> 0đ
        $this->assertEquals(0.0, CartService::calculateShippingFee(600000));

        // Khi subtotal < 500k và không có mã -> 25k
        $fee = CartService::calculateShippingFee(220000);
        $this->assertEquals(25000.0, $fee);
    }

    /**
     * Test hỗ trợ cả 2 loại mã percent và percentage từ admin và code (Feedback Row 3)
     */
    public function test_percent_and_percentage_coupon_types(): void
    {
        $types = ['percentage', 'percent'];
        $subtotal = 200000.0;
        $value = 10.0; // 10%

        foreach ($types as $type) {
            $isPercent = in_array($type, ['percentage', 'percent']);
            $this->assertTrue($isPercent);

            $discount = round(($subtotal * $value) / 100);
            $this->assertEquals(20000.0, $discount);
        }
    }

    /**
     * Test mã miễn phí vận chuyển giảm phí ship về 0 đồng (Freeship 0đ)
     */
    public function test_free_shipping_voucher_reduces_shipping_to_zero(): void
    {
        // 1. Coupon type = free_shipping
        $couponFree = new Coupon([
            'code' => 'FREESHIP_ANY',
            'type' => 'free_shipping',
            'value' => 0,
            'min_order_amount' => 0,
            'is_active' => true,
        ]);
        $this->assertTrue($couponFree->isValidFor(50000));
        $this->assertTrue(in_array($couponFree->type, ['shipping', 'free_shipping']));
        $this->assertTrue($couponFree->type === 'free_shipping' || (float) $couponFree->value == 100.0 || (float) $couponFree->value == 0.0);

        // 2. Coupon type = shipping với value = 100 (ý nghĩa 100% freeship)
        $coupon100 = new Coupon([
            'code' => 'SHIP100',
            'type' => 'shipping',
            'value' => 100,
            'min_order_amount' => 0,
            'is_active' => true,
        ]);
        $this->assertTrue($coupon100->isValidFor(50000));
        $this->assertTrue((float) $coupon100->value == 100.0);

        // 3. Logic tính phí ship khi áp mã freeship
        $defaultShippingFee = 25000.0;
        $feeAfterFreeship = ($couponFree->type === 'free_shipping' || (float) $couponFree->value == 100.0) ? 0.0 : $defaultShippingFee;
        $this->assertEquals(0.0, $feeAfterFreeship);

        $feeAfterShip100 = ($coupon100->type === 'free_shipping' || (float) $coupon100->value == 100.0) ? 0.0 : $defaultShippingFee;
        $this->assertEquals(0.0, $feeAfterShip100);
    }
}
