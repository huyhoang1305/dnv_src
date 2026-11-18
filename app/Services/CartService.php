<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartService
{
    const SESSION_KEY = 'cart_items';
    const COOKIE_NAME = 'dnv_cart_token';

    /**
     * Lấy định danh giỏ hàng duy nhất cho trình duyệt/user hiện tại (tránh xung đột ghi đè session khi bấm nhanh nhiều lần)
     */
    protected static function getCartCacheKey(): string
    {
        $token = request()->cookie(self::COOKIE_NAME) ?: session()->get('dnv_cart_token');

        if (empty($token)) {
            $token = (string) Str::uuid();
            session()->put('dnv_cart_token', $token);
            Cookie::queue(self::COOKIE_NAME, $token, 60 * 24 * 30);
        } elseif (!session()->has('dnv_cart_token')) {
            session()->put('dnv_cart_token', $token);
        }

        return 'dnv_cart_v2_' . $token;
    }

    protected static function saveItems(array $items): array
    {
        $items = array_values($items);
        Cache::put(self::getCartCacheKey(), $items, now()->addDays(30));
        session()->put(self::SESSION_KEY, $items);
        session()->save();

        return $items;
    }

    /**
     * Lấy danh sách sản phẩm trong giỏ hàng
     */
    public static function getItems(): array
    {
        $cacheKey = self::getCartCacheKey();
        $items = Cache::get($cacheKey);

        if (!is_array($items)) {
            $sessionItems = session()->get(self::SESSION_KEY, []);
            $items = is_array($sessionItems) ? array_values($sessionItems) : [];
            Cache::put($cacheKey, $items, now()->addDays(30));
        }

        // Luôn lọc bỏ sản phẩm giá <= 0đ nếu còn sót trong giỏ cũ
        return array_values(array_filter($items, fn ($item) => (float) ($item['price'] ?? 0) > 0));
    }

    /**
     * Đồng bộ và tái xác thực toàn bộ giỏ hàng theo dữ liệu gốc mới nhất từ Database
     * Đảm bảo giá tiền, trạng thái kích hoạt, tồn kho, tên, hình ảnh luôn chuẩn 100% từ Database máy chủ
     */
    public static function syncWithDatabase(): array
    {
        $rawItems = self::getItems();
        if (empty($rawItems)) {
            return [];
        }

        $productIds = collect($rawItems)->pluck('id')->filter()->unique()->values()->all();
        $products = Product::with(['brand', 'variants.attributeValues'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $validatedItems = [];
        $changed = false;

        foreach ($rawItems as $item) {
            $productId = (int) ($item['id'] ?? 0);
            $product = $products->get($productId);

            if (!$product || !$product->is_active) {
                $changed = true;
                continue;
            }

            $variantId = !empty($item['variant_id']) ? (int) $item['variant_id'] : null;
            $variant = null;
            if ($variantId) {
                $variant = $product->variants->firstWhere('id', $variantId);
                if (!$variant) {
                    $changed = true;
                    continue;
                }
            }

            $authoritativePrice = $variant
                ? (float) $variant->effective_price
                : (float) (($product->sale_price && $product->sale_price > 0) ? $product->sale_price : $product->base_price);

            if ($authoritativePrice <= 0) {
                $changed = true;
                continue;
            }

            $qty = max(1, (int) ($item['qty'] ?? 1));
            $variantLabel = $variant?->label;
            $packing = $variantLabel ?: ($product->weight ?: ($product->unit ?: 'Tiêu chuẩn'));
            $imageRaw = ($variant && !empty($variant->image_url)) ? $variant->image_url : $product->image_url;
            $imageUrl = curator_url($imageRaw, 400, 400) ?: asset('storage/logo/favicon-dnv.png');

            if (
                (float) ($item['price'] ?? 0) !== $authoritativePrice ||
                ($item['name'] ?? '') !== $product->name ||
                (int) ($item['qty'] ?? 0) !== $qty
            ) {
                $changed = true;
            }

            $validatedItems[] = [
                'id' => $product->id,
                'variant_id' => $variant?->id,
                'variant_name' => $variantLabel,
                'name' => $product->name,
                'slug' => $product->slug,
                'brand' => $product->brand?->name ?? 'DNV',
                'price' => $authoritativePrice,
                'original_price' => (float) $product->base_price,
                'qty' => $qty,
                'image' => $imageUrl,
                'packing' => $packing,
            ];
        }

        if ($changed || count($validatedItems) !== count($rawItems)) {
            self::saveItems($validatedItems);
        }

        return $validatedItems;
    }

    /**
     * TÍNH TOÁN VÀ TÁI XÁC THỰC TOÀN BỘ TÀI CHÍNH ĐƠN HÀNG HOÀN TOÀN Ở PHÍA MÁY CHỦ (SERVER-SIDE)
     * Ngăn chặn 100% mọi hành vi sửa giá từ DevTools, sửa Livewire payload hay can thiệp DOM
     */
    public static function calculateAuthoritativeTotals(array $rawItems, ?string $voucherCode = null): array
    {
        if (empty($rawItems)) {
            return [
                'success' => false,
                'message' => 'Giỏ hàng đang trống. Vui lòng thêm sản phẩm vào giỏ hàng trước khi đặt hàng.',
            ];
        }

        $productIds = collect($rawItems)->pluck('id')->filter()->unique()->values()->all();
        $products = Product::with(['brand', 'variants.attributeValues'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $verifiedItems = [];
        $subtotal = 0;

        foreach ($rawItems as $item) {
            $productId = (int) ($item['id'] ?? 0);
            $product = $products->get($productId);

            if (!$product || !$product->is_active) {
                $pName = $item['name'] ?? 'Sản phẩm đã chọn';
                return [
                    'success' => false,
                    'message' => "Sản phẩm \"{$pName}\" hiện đã ngừng kinh doanh hoặc không còn bán. Vui lòng cập nhật lại giỏ hàng.",
                ];
            }

            $variantId = !empty($item['variant_id']) ? (int) $item['variant_id'] : null;
            $variant = null;
            if ($variantId) {
                $variant = $product->variants->firstWhere('id', $variantId);
                if (!$variant) {
                    return [
                        'success' => false,
                        'message' => "Phân loại hàng của sản phẩm \"{$product->name}\" không còn tồn tại.",
                    ];
                }
            }

            // Lấy giá chuẩn tuyệt đối từ Database (bỏ qua hoàn toàn giá client gửi lên)
            $authoritativePrice = $variant
                ? (float) $variant->effective_price
                : (float) (($product->sale_price && $product->sale_price > 0) ? $product->sale_price : $product->base_price);

            if ($authoritativePrice <= 0) {
                return [
                    'success' => false,
                    'message' => "Sản phẩm \"{$product->name}\" chưa được thiết lập giá hợp lệ.",
                ];
            }

            $qty = max(1, (int) ($item['qty'] ?? 1));

            // Kiểm tra tồn kho sản phẩm / biến thể (Issue #13)
            $availableStock = $variant !== null
                ? (int) ($variant->stock ?? 0)
                : (int) ($product->stock_quantity ?? 0);

            if ($availableStock <= 0) {
                return [
                    'success' => false,
                    'message' => "Sản phẩm \"{$product->name}\"" . ($variant ? " ({$variant->label})" : '') . " hiện đã hết hàng.",
                ];
            }

            if ($qty > $availableStock) {
                return [
                    'success' => false,
                    'message' => "Sản phẩm \"{$product->name}\"" . ($variant ? " ({$variant->label})" : '') . " chỉ còn {$availableStock} sản phẩm trong kho (bạn chọn {$qty}).",
                ];
            }

            $lineTotal = $authoritativePrice * $qty;
            $subtotal += $lineTotal;

            $variantLabel = $variant?->label;
            $fullName = $product->name . ($variantLabel ? ' (' . $variantLabel . ')' : '');
            $packing = $variantLabel ?: ($product->weight ?: ($product->unit ?: 'Tiêu chuẩn'));
            $imageRaw = ($variant && !empty($variant->image_url)) ? $variant->image_url : $product->image_url;
            $imageUrl = curator_url($imageRaw, 400, 400) ?: asset('storage/logo/favicon-dnv.png');

            $verifiedItems[] = [
                'id' => $product->id,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'variant_name' => $variantLabel,
                'name' => $product->name,
                'full_name' => $fullName,
                'slug' => $product->slug,
                'brand' => $product->brand?->name ?? 'DNV',
                'price' => $authoritativePrice,
                'original_price' => (float) $product->base_price,
                'qty' => $qty,
                'total' => $lineTotal,
                'image' => $imageUrl,
                'packing' => $packing,
            ];
        }

        // TÁI XÁC THỰC MÃ GIẢM GIÁ TỪ DATABASE (Issue #4, #14 & #3 trong đợt test 2)
        $discount = 0;
        $coupon = null;
        $cleanVoucher = strtoupper(trim((string) ($voucherCode ?: self::getVoucher())));

        // Tính toán phí ship chuẩn thống nhất (Row 2 feedback)
        $shippingFee = self::calculateShippingFee($subtotal, $cleanVoucher);

        if (!empty($cleanVoucher)) {
            $coupon = Coupon::where('code', $cleanVoucher)->first();
            $couponError = null;

            if ($coupon && $coupon->isValidFor($subtotal, $couponError)) {
                $isShippingCoupon = in_array($coupon->type, ['shipping', 'free_shipping'])
                    || in_array($coupon->code, ['FREESHIP', 'FREESHIP25', 'FREESHIP40', 'SHIP25'])
                    || str_starts_with($coupon->code, 'GIAMSHIP')
                    || str_contains(strtoupper($coupon->code), 'FREESHIP');

                if ($isShippingCoupon) {
                    // Mã giảm phí vận chuyển: đã được tính trừ vào shippingFee thông qua calculateShippingFee
                    $discount = 0;
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

                $discount = min($subtotal, max(0, $discount));
            } else {
                $coupon = null;
                $discount = 0;
            }
        }

        $total = max(0, $subtotal - $discount + $shippingFee);

        return [
            'success' => true,
            'items' => $verifiedItems,
            'subtotal' => (float) $subtotal,
            'discount' => (float) $discount,
            'shipping_fee' => (float) $shippingFee,
            'total' => (float) $total,
            'coupon' => $coupon,
            'coupon_code' => $coupon?->code,
        ];
    }

    /**
     * Hàm dùng chung tính toán phí vận chuyển cho toàn hệ thống (màn hình Cart, Checkout và Server lưu đơn hàng)
     */
    public static function calculateShippingFee(float $subtotal, ?string $voucherCode = null): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        $setting = Setting::getSystemSetting();
        $defaultShippingFee = (float) ($setting?->general_settings['shipping']['default_fee'] ?? 25000);
        $freeShippingThreshold = (float) ($setting?->general_settings['shipping']['free_shipping_threshold'] 
            ?? ($setting?->general_settings['shipping']['free_threshold'] ?? 500000));

        if ($subtotal >= $freeShippingThreshold) {
            return 0.0;
        }

        $shippingFee = $defaultShippingFee;

        if (!empty($voucherCode)) {
            $coupon = Coupon::where('code', strtoupper(trim((string) $voucherCode)))->first();
            $couponError = null;
            if ($coupon && $coupon->isValidFor($subtotal, $couponError)) {
                $isShippingCoupon = in_array($coupon->type, ['shipping', 'free_shipping'])
                    || in_array($coupon->code, ['FREESHIP', 'FREESHIP25', 'FREESHIP40', 'SHIP25'])
                    || str_starts_with($coupon->code, 'GIAMSHIP')
                    || str_contains(strtoupper($coupon->code), 'FREESHIP');

                if ($isShippingCoupon) {
                    // Miễn phí vận chuyển hoàn toàn (về 0 đồng):
                    // 1. Loại 'free_shipping'
                    // 2. Giá trị giảm = 0 hoặc = 100 (100% freeship)
                    // 3. Tên mã có chứa FREESHIP hoặc SHIP25/SHIP40
                    if (
                        $coupon->type === 'free_shipping'
                        || (float) $coupon->value == 100.0
                        || (float) $coupon->value == 0.0
                        || in_array($coupon->code, ['FREESHIP', 'FREESHIP25', 'FREESHIP40', 'SHIP25'])
                        || str_contains(strtoupper($coupon->code), 'FREESHIP')
                    ) {
                        return 0.0;
                    }

                    // Giảm phí ship cố định (VD: giảm 20.000đ ship như mã GIAMSHIP20K)
                    $val = (float) $coupon->value;
                    if ($coupon->max_discount && (float) $coupon->max_discount > 0 && $val > (float) $coupon->max_discount) {
                        $val = (float) $coupon->max_discount;
                    }
                    $shippingFee = max(0.0, $shippingFee - $val);
                }
            }
        }

        return (float) $shippingFee;
    }

    /**
     * Thêm sản phẩm (kèm biến thể nếu có) vào giỏ hàng - Khóa nguyên tử chống mất món khi click liên tiếp ở trang chủ
     */
    public static function add(Product|int $product, int $qty = 1, ?int $variantId = null): array
    {
        $lockKey = 'lock_' . self::getCartCacheKey();

        return Cache::lock($lockKey, 5)->block(5, function () use ($product, $qty, $variantId) {
            if (is_numeric($product)) {
                $product = Product::with(['brand', 'variants.attributeValues'])->find($product);
            } else {
                $product->loadMissing(['brand', 'variants.attributeValues']);
            }

            if (!$product || !$product->is_active) {
                return self::getItems();
            }

            $variant = null;
            if ($variantId) {
                $variant = $product->variants->firstWhere('id', (int) $variantId)
                    ?? ProductVariant::with('attributeValues')->where('product_id', $product->id)->find($variantId);
            } elseif ($product->variants->isNotEmpty()) {
                // Nếu thêm nhanh từ trang chủ/danh mục mà sản phẩm có biến thể, chọn biến thể đầu tiên mặc định
                $variant = $product->variants->sortBy('sort_order')->first();
            }

            $price = $variant
                ? $variant->effective_price
                : (float) (($product->sale_price && $product->sale_price > 0) ? $product->sale_price : $product->base_price);

            // Chặn tuyệt đối sản phẩm giá 0 đồng
            if ($price <= 0) {
                return self::getItems();
            }

            $effectiveVariantId = $variant?->id;
            $variantLabel = $variant?->label;

            $imageRaw = ($variant && !empty($variant->image_url)) ? $variant->image_url : $product->image_url;
            $imageUrl = curator_url($imageRaw, 400, 400) ?: asset('storage/logo/favicon-dnv.png');

            $items = self::getItems();
            $foundIndex = -1;

            foreach ($items as $idx => $item) {
                $itemVarId = $item['variant_id'] ?? null;
                if ((int) $item['id'] === (int) $product->id && (int) ($itemVarId ?? 0) === (int) ($effectiveVariantId ?? 0)) {
                    $foundIndex = $idx;
                    break;
                }
            }

            $packing = $variantLabel ?: ($product->weight ?: ($product->unit ?: 'Tiêu chuẩn'));

            // Kiểm tra và giới hạn theo tồn kho thực tế (Issue #13)
            $availableStock = $variant !== null
                ? (int) ($variant->stock ?? 0)
                : (int) ($product->stock_quantity ?? 0);

            if ($availableStock <= 0) {
                return self::getItems();
            }

            if ($foundIndex >= 0) {
                $newQty = $items[$foundIndex]['qty'] + max(1, $qty);
                $items[$foundIndex]['qty'] = min($newQty, $availableStock);
                $items[$foundIndex]['price'] = $price;
                $items[$foundIndex]['packing'] = $packing;
                $items[$foundIndex]['variant_name'] = $variantLabel;
            } else {
                $items[] = [
                    'id' => $product->id,
                    'variant_id' => $effectiveVariantId,
                    'variant_name' => $variantLabel,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'brand' => $product->brand?->name ?? 'DNV',
                    'price' => $price,
                    'original_price' => (float) $product->base_price,
                    'qty' => min(max(1, $qty), $availableStock),
                    'image' => $imageUrl,
                    'packing' => $packing,
                ];
            }

            return self::saveItems($items);
        });
    }

    /**
     * Đổi biến thể ngay trong giỏ hàng và cập nhật lại giá tương ứng
     */
    public static function changeVariant(int $index, int $newVariantId): array
    {
        $lockKey = 'lock_' . self::getCartCacheKey();

        return Cache::lock($lockKey, 5)->block(5, function () use ($index, $newVariantId) {
            $items = self::getItems();
            if (!isset($items[$index])) {
                return $items;
            }

            $productId = (int) $items[$index]['id'];
            $product = Product::with(['brand', 'variants.attributeValues'])->find($productId);
            if (!$product) {
                return $items;
            }

            $variant = $product->variants->firstWhere('id', $newVariantId);
            if (!$variant) {
                return $items;
            }

            $price = $variant->effective_price;
            if ($price <= 0) {
                return $items;
            }

            // Kiểm tra nếu biến thể mới đã có sẵn ở dòng khác trong giỏ thì gộp số lượng
            foreach ($items as $otherIdx => $otherItem) {
                if ($otherIdx !== $index
                    && (int) $otherItem['id'] === $productId
                    && (int) ($otherItem['variant_id'] ?? 0) === (int) $variant->id
                ) {
                    $items[$otherIdx]['qty'] += (int) ($items[$index]['qty'] ?? 1);
                    array_splice($items, $index, 1);
                    return self::saveItems($items);
                }
            }

            $imageRaw = !empty($variant->image_url) ? $variant->image_url : $product->image_url;
            $items[$index]['variant_id'] = $variant->id;
            $items[$index]['variant_name'] = $variant->label;
            $items[$index]['packing'] = $variant->label;
            $items[$index]['price'] = $price;
            $items[$index]['image'] = curator_url($imageRaw, 400, 400) ?: $items[$index]['image'];

            return self::saveItems($items);
        });
    }

    /**
     * Tăng số lượng sản phẩm
     */
    public static function increment(int $index): array
    {
        $lockKey = 'lock_' . self::getCartCacheKey();

        return Cache::lock($lockKey, 5)->block(5, function () use ($index) {
            $items = self::getItems();
            if (isset($items[$index])) {
                $productId = (int) ($items[$index]['id'] ?? 0);
                $variantId = !empty($items[$index]['variant_id']) ? (int) $items[$index]['variant_id'] : null;
                $stock = 999;
                if ($variantId && ($variant = ProductVariant::find($variantId))) {
                    $stock = (int) ($variant->stock ?? 0);
                } elseif ($productId && ($product = Product::find($productId))) {
                    $stock = (int) ($product->stock_quantity ?? 0);
                }

                if ($items[$index]['qty'] < $stock) {
                    $items[$index]['qty']++;
                    return self::saveItems($items);
                }
            }
            return $items;
        });
    }

    /**
     * Giảm số lượng sản phẩm
     */
    public static function decrement(int $index): array
    {
        $lockKey = 'lock_' . self::getCartCacheKey();

        return Cache::lock($lockKey, 5)->block(5, function () use ($index) {
            $items = self::getItems();
            if (isset($items[$index])) {
                if ($items[$index]['qty'] > 1) {
                    $items[$index]['qty']--;
                } else {
                    array_splice($items, $index, 1);
                }
                return self::saveItems($items);
            }
            return $items;
        });
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public static function remove(int $index): array
    {
        $lockKey = 'lock_' . self::getCartCacheKey();

        return Cache::lock($lockKey, 5)->block(5, function () use ($index) {
            $items = self::getItems();
            if (isset($items[$index])) {
                array_splice($items, $index, 1);
                return self::saveItems($items);
            }
            return $items;
        });
    }

    /**
     * Xóa sạch giỏ hàng
     */
    public static function clear(): void
    {
        self::saveItems([]);
    }

    /**
     * Đếm tổng số lượng sản phẩm trong giỏ
     */
    public static function count(): int
    {
        $items = self::getItems();
        $totalQty = 0;
        foreach ($items as $item) {
            $totalQty += ($item['qty'] ?? 1);
        }
        return $totalQty;
    }

    /**
     * Tính tạm tính tổng tiền
     */
    public static function subtotal(): float
    {
        $items = self::getItems();
        $sum = 0;
        foreach ($items as $item) {
            $sum += (($item['price'] ?? 0) * ($item['qty'] ?? 1));
        }
        return (float) $sum;
    }

    /**
     * Quản lý mã voucher trong session
     */
    public static function setVoucher(string $code): void
    {
        session()->put('applied_voucher_code', strtoupper(trim($code)));
        session()->save();
    }

    public static function getVoucher(): ?string
    {
        return session()->get('applied_voucher_code', null);
    }

    public static function removeVoucher(): void
    {
        session()->forget('applied_voucher_code');
        session()->save();
    }
}
