<?php

namespace App\Livewire;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\GhnService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public $setting;

    public $items = [];
    public $availableCoupons = [];
    public $customer_name = '';
    public $customer_phone = '';
    public $customer_email = '';

    // GHN Location Data
    public $provinces = [];
    public $districts = [];
    public $wards = [];

    public $province_id = 202; // Default TP.HCM
    public $district_id = 1442; // Default Quận 1
    public $ward_code = '20102'; // Default Phường Bến Thành

    public $shipping_city = 'Hồ Chí Minh';
    public $shipping_district = 'Quận 1';
    public $shipping_ward = 'Phường Bến Thành';
    public $shipping_address = '';
    public $order_notes = '';
    public $delivery_method = 'standard';
    public $payment_method = 'cod'; // Only COD

    public $voucherCode = '';
    public $voucherDiscount = 0;
    public $appliedVoucher = null;
    public $appliedCouponData = null;
    public $errorMessage = '';

    public function mount()
    {
        $this->setting = Setting::getSystemSetting();

        // Autofill if logged in
        if (Auth::check()) {
            $user = Auth::user();
            $this->customer_name = $user->name ?? '';
            $this->customer_phone = $user->phone ?? '';
            $this->shipping_address = $user->address ?? '';
            if ($user->email && !str_ends_with($user->email, '@thv.vn') && !str_ends_with($user->email, '@dnv1.biz')) {
                $this->customer_email = $user->email;
            }
            if (empty($this->customer_phone) && $user->email && (str_ends_with($user->email, '@thv.vn') || str_ends_with($user->email, '@dnv1.biz'))) {
                $this->customer_phone = preg_replace('/@(thv\.vn|dnv1\.biz)$/', '', $user->email);
            }
        }

        // Initialize GHN Address Data
        $ghn = new GhnService();
        $this->provinces = $ghn->getProvinces();
        $this->districts = $ghn->getDistricts($this->province_id);
        $this->wards = $ghn->getWards($this->district_id);

        // Load available coupons
        $this->availableCoupons = Coupon::where('is_active', true)->orderBy('sort_order', 'asc')->get();

        // Load items from CartService và đồng bộ giá chuẩn mới nhất từ Database
        $this->items = CartService::syncWithDatabase();

        // Check if there is a pre-applied voucher from Cart
        $savedVoucher = CartService::getVoucher();
        if ($savedVoucher) {
            $this->applyVoucher($savedVoucher);
        }
    }

    public function updatedProvinceId($value)
    {
        $this->province_id = (int) $value;
        $this->district_id = null;
        $this->ward_code = null;
        $this->shipping_district = '';
        $this->shipping_ward = '';
        $this->districts = [];
        $this->wards = [];

        foreach ($this->provinces as $p) {
            if ($p['id'] == $this->province_id) {
                $this->shipping_city = $p['name'];
                break;
            }
        }

        if ($this->province_id > 0) {
            $this->districts = (new GhnService())->getDistricts($this->province_id);
            $this->district_id = null;
            $this->shipping_district = '';
            $this->wards = [];
            $this->ward_code = null;
            $this->shipping_ward = '';
        } else {
            $this->shipping_city = '';
        }
    }

    public function updatedDistrictId($value)
    {
        $this->district_id = (int) $value;
        $this->ward_code = null;
        $this->shipping_ward = '';
        $this->wards = [];

        foreach ($this->districts as $d) {
            if ($d['id'] == $this->district_id) {
                $this->shipping_district = $d['name'];
                break;
            }
        }

        if ($this->district_id > 0) {
            $this->wards = (new GhnService())->getWards($this->district_id);
            $this->ward_code = null;
            $this->shipping_ward = '';
        } else {
            $this->shipping_district = '';
        }
    }

    public function updatedWardCode($value)
    {
        $this->ward_code = (string) $value;
        foreach ($this->wards as $w) {
            if ($w['code'] == $this->ward_code) {
                $this->shipping_ward = $w['name'];
                break;
            }
        }
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

    public function applyVoucher($code = null)
    {
        $this->errorMessage = '';
        $inputCode = strtoupper(trim((string) ($code ?? $this->voucherCode)));

        if (empty($inputCode)) {
            $this->errorMessage = 'Vui lòng nhập mã ưu đãi.';
            return;
        }

        $coupon = Coupon::where('code', $inputCode)->first();
        if (!$coupon) {
            $this->errorMessage = 'Mã giảm giá không tồn tại hoặc đã hết hạn.';
            return;
        }

        $subtotal = $this->getSubtotalProperty();
        $errorMsg = null;
        if (!$coupon->isValidFor($subtotal, $errorMsg)) {
            $this->errorMessage = $errorMsg ?: 'Mã giảm giá không hợp lệ.';
            return;
        }

        $isShippingCoupon = in_array($coupon->type, ['shipping', 'free_shipping'])
            || in_array($coupon->code, ['FREESHIP', 'FREESHIP25', 'FREESHIP40', 'SHIP25'])
            || str_starts_with($coupon->code, 'GIAMSHIP')
            || str_contains(strtoupper($coupon->code), 'FREESHIP');

        $discount = 0;
        if ($isShippingCoupon) {
            $discount = 0; // Handled directly via shipping discount
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
        $this->errorMessage = '';
        CartService::setVoucher($coupon->code);
    }

    public function removeVoucher()
    {
        $this->appliedVoucher = null;
        $this->appliedCouponData = null;
        $this->voucherDiscount = 0;
        $this->errorMessage = '';
        CartService::removeVoucher();
    }

    public function submitOrder()
    {
        $this->errorMessage = '';

        $throttleKey = 'order_submit:' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->errorMessage = "Bạn đã gửi yêu cầu đặt hàng quá nhanh. Vui lòng thử lại sau {$seconds} giây.";
            return;
        }

        if (empty($this->items)) {
            $this->errorMessage = 'Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm trước khi đặt hàng.';
            return;
        }

        if (empty(trim($this->customer_name))) {
            $this->errorMessage = 'Vui lòng nhập họ và tên người nhận.';
            return;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', trim((string) $this->customer_phone));
        if (strlen($cleanPhone) !== 10 || !str_starts_with($cleanPhone, '0')) {
            $this->errorMessage = 'Số điện thoại người nhận không hợp lệ (yêu cầu đúng 10 số và bắt đầu bằng số 0).';
            return;
        }
        $this->customer_phone = $cleanPhone;

        if (!empty(trim((string) $this->customer_email)) && !filter_var(trim((string) $this->customer_email), FILTER_VALIDATE_EMAIL)) {
            $this->errorMessage = 'Địa chỉ email người nhận không đúng định dạng.';
            return;
        }

        if (empty($this->province_id) || empty($this->shipping_city)) {
            $this->errorMessage = 'Vui lòng chọn Tỉnh / Thành phố.';
            return;
        }

        if (empty(trim((string) $this->shipping_district))) {
            $this->errorMessage = 'Vui lòng chọn hoặc nhập Quận / Huyện.';
            return;
        }

        if (empty(trim((string) $this->shipping_ward))) {
            $this->errorMessage = 'Vui lòng chọn hoặc nhập Phường / Xã.';
            return;
        }

        if (empty(trim($this->shipping_address))) {
            $this->errorMessage = 'Vui lòng nhập địa chỉ giao hàng cụ thể (số nhà, tên đường).';
            return;
        }

        // 1. TÁI XÁC THỰC TOÀN BỘ GIÁ TIỀN, MÃ GIẢM GIÁ, PHÍ SHIP TRÊN MÁY CHỦ (SERVER-SIDE)
        // Bỏ qua hoàn toàn mọi con số do trình duyệt gửi lên, tính lại trực tiếp từ Database
        $financials = CartService::calculateAuthoritativeTotals($this->items, $this->appliedVoucher ?: $this->voucherCode);
        if (!$financials['success']) {
            $this->errorMessage = $financials['message'];
            // Tự động đồng bộ lại giỏ hàng về đúng dữ liệu chuẩn
            $this->items = CartService::syncWithDatabase();
            return;
        }

        $authoritativeItems = $financials['items'];
        $subtotal = $financials['subtotal'];
        $discount = $financials['discount'];
        $shippingFee = $financials['shipping_fee'];
        $total = $financials['total'];
        $coupon = $financials['coupon'];

        $this->payment_method = 'cod';
        $orderNumber = Order::generateOrderNumber();
        $fullAddress = trim($this->shipping_address) . ', ' . $this->shipping_ward . ', ' . $this->shipping_district . ', ' . $this->shipping_city;

        $orderData = [
            'order_number' => $orderNumber,
            'customer_name' => trim($this->customer_name),
            'customer_phone' => trim($this->customer_phone),
            'customer_email' => trim($this->customer_email),
            'shipping_address' => $fullAddress,
            'delivery_method' => $this->delivery_method,
            'payment_method' => 'cod',
            'order_notes' => $this->order_notes,
            'items' => $authoritativeItems,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping_fee' => $shippingFee,
            'total' => $total,
            'created_at' => now()->format('H:i d/m/Y'),
        ];

        // 2. LƯU ĐƠN HÀNG VỚI GIÁ THẬT ĐÃ TÍNH TOÁN TRONG TRANSACTION
        try {
            RateLimiter::hit($throttleKey, 60);
            DB::transaction(function () use ($orderNumber, $fullAddress, $subtotal, $discount, $coupon, $shippingFee, $total, $authoritativeItems) {
                if ($coupon) {
                    $lockedCoupon = Coupon::lockForUpdate()->find($coupon->id);
                    if (!$lockedCoupon || ($lockedCoupon->usage_limit > 0 && $lockedCoupon->used_count >= $lockedCoupon->usage_limit)) {
                        throw new \RuntimeException('Mã giảm giá vừa hết lượt sử dụng.');
                    }
                    $lockedCoupon->increment('used_count');
                }

                $order = Order::create([
                    'user_id' => Auth::id() ?? null,
                    'order_number' => $orderNumber,
                    'status' => 'pending',
                    'sub_total' => $subtotal,
                    'discount_amount' => $discount,
                    'coupon_id' => $coupon?->id,
                    'shipping_fee' => $shippingFee,
                    'total_amount' => $total,
                    'customer_name' => trim($this->customer_name),
                    'customer_phone' => trim($this->customer_phone),
                    'customer_email' => trim($this->customer_email),
                    'shipping_address' => $fullAddress,
                    'order_notes' => $this->order_notes,
                    'delivery_method' => $this->delivery_method,
                    'payment_method' => 'cod',
                    'payment_status' => 'unpaid',
                ]);

                foreach ($authoritativeItems as $item) {
                    $qty = (int) $item['qty'];
                    $price = (float) $item['price'];

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['id'] ?? null,
                        'product_variant_id' => $item['variant_id'] ?? null,
                        'product_name' => $item['full_name'],
                        'product_image' => $item['image'] ?? null,
                        'price' => $price,
                        'quantity' => $qty,
                        'total' => $price * $qty,
                    ]);

                    if (!empty($item['id']) && ($prod = Product::lockForUpdate()->find($item['id']))) {
                        $prod->sold_count = (int) ($prod->sold_count ?? 0) + $qty;
                        $prod->stock_quantity = max(0, (int) ($prod->stock_quantity ?? 0) - $qty);
                        $prod->save();
                    }

                    if (!empty($item['variant_id']) && ($var = \App\Models\ProductVariant::lockForUpdate()->find($item['variant_id']))) {
                        $var->stock = max(0, (int) ($var->stock ?? 0) - $qty);
                        $var->save();
                    }
                }
            });
        } catch (\RuntimeException $re) {
            $this->errorMessage = $re->getMessage();
            return;
        } catch (\Throwable $e) {
            Log::error('Order creation error: ' . $e->getMessage());
            $this->errorMessage = 'Có lỗi xảy ra khi tạo đơn hàng. Vui lòng thử lại sau.';
            return;
        }

        // Lưu dữ liệu vào session cho trang Hoàn tất đơn hàng
        session()->put('latest_order', $orderData);

        // Xóa giỏ hàng và voucher sau khi đặt thành công
        CartService::clear();
        CartService::removeVoucher();
        $this->dispatch('cart-updated');

        return $this->redirect(route('order.success', ['orderCode' => $orderNumber]), navigate: true);
    }
};
