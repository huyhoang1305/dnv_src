<div class="min-h-screen bg-[#f8faf9] pt-2.5 pb-8 sm:py-8 selection:bg-[#CE171F] selection:text-white" x-data="{ toast: @entangle('showToast') }">
    <!-- Page SEO Meta Tags -->
    <livewire:seo.cart />

    @php
        $cart = $setting?->interface_settings['cart'] ?? [];
        $cartTitle = !empty($cart['title']) ? $cart['title'] : 'Giỏ hàng của bạn';
        $threshold = (int)($cart['freeship_threshold'] ?? 0);
        $trustBadges = $cart['trust_badges'] ?? [];
    @endphp

    <!-- Breadcrumb Navigation -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-2.5 sm:mb-5">
        <nav class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-slate-500 font-normal whitespace-nowrap overflow-hidden">
            <a href="{{ route('home') }}" wire:navigate.hover class="hover:text-[#CE171F] transition-colors shrink-0">Trang chủ</a>
            <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-300 shrink-0"></i>
            <span class="text-slate-800 font-medium sm:font-semibold min-w-0 truncate">Giỏ hàng</span>
        </nav>
    </div>

    <!-- Toast Notification -->
    <div 
        x-show="toast" 
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        @click="toast = false"
        x-init="$watch('toast', val => { if(val) setTimeout(() => toast = false, 2800) })"
        class="fixed bottom-6 right-6 z-50 bg-[#1b3524] text-white px-4 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-emerald-600/40 cursor-pointer"
        style="display: none;"
    >
        <div class="w-7 h-7 rounded-full bg-red-500 flex items-center justify-center text-white text-xs shrink-0">
            <i class="fa-solid fa-check"></i>
        </div>
        <div class="text-xs font-semibold">
            {{ $toastMessage }}
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Title and Counter -->
        <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-2 pb-3 mb-3.5 sm:pb-4 sm:mb-6 border-b border-slate-200/80">
            <h1 class="text-[15px] sm:text-xl md:text-2xl font-bold text-[#991B1B] sm:text-slate-900 tracking-tight leading-snug">
                {{ $cartTitle }}
            </h1>

            <div class="flex items-center gap-2.5 shrink-0 whitespace-nowrap ml-auto">
                <span class="bg-emerald-100 text-[#CE171F] text-[11px] sm:text-xs font-bold px-2.5 py-0.5 rounded-full whitespace-nowrap shrink-0">
                    {{ count($items) }} món
                </span>

                @if (count($items) > 0)
                    <button 
                        wire:click="clearCart" 
                        wire:confirm="Bạn có chắc chắn muốn xóa toàn bộ sản phẩm trong giỏ hàng?"
                        type="button" 
                        class="text-[11px] sm:text-xs text-rose-600 hover:text-rose-700 font-semibold inline-flex items-center gap-1 transition cursor-pointer whitespace-nowrap shrink-0"
                    >
                        <i class="fa-regular fa-trash-can text-[11px]"></i>
                        <span>Xóa tất cả</span>
                    </button>
                @endif
            </div>
        </div>

        @if (count($items) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

                <!-- Left Column: Products List (8 cols) -->
                <div class="lg:col-span-8 space-y-4">

                    <!-- Free Shipping Milestone Progress Bar -->
                    @if ($threshold > 0)
                        @php
                            $sub = $this->subtotal;
                            $percent = min(100, round(($sub / $threshold) * 100));
                            $remain = max(0, $threshold - $sub);
                            $progressTpl = $cart['freeship_text_progress'] ?? 'Mua thêm {remain} để được MIỄN PHÍ VẬN CHUYỂN';
                            $progressText = str_replace('{remain}', number_format($remain, 0, ',', '.') . 'đ', $progressTpl);
                            $reachedText = !empty($cart['freeship_text_reached']) ? $cart['freeship_text_reached'] : '🎉 Bạn đã đủ điều kiện nhận MIỄN PHÍ VẬN CHUYỂN TOÀN QUỐC!';
                        @endphp
                        <div class="bg-white rounded-2xl p-3 sm:p-5 border border-emerald-100 shadow-2xs space-y-2">
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2 font-semibold text-slate-800 min-w-0">
                                    <span class="w-6 h-6 rounded-lg bg-emerald-100 text-[#CE171F] flex items-center justify-center text-xs shrink-0">
                                        <i class="fa-solid fa-truck-fast"></i>
                                    </span>
                                    @if ($remain > 0)
                                        <span class="text-[11.5px] sm:text-xs leading-snug">{!! $progressText !!}</span>
                                    @else
                                        <span class="text-[#991B1B] font-bold text-[11.5px] sm:text-xs leading-snug">{{ $reachedText }}</span>
                                    @endif
                                </div>
                                <span class="text-[11px] font-bold text-slate-500 shrink-0">{{ $percent }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div 
                                    class="bg-gradient-to-r from-emerald-500 to-[#4d835f] h-full rounded-full transition-all duration-500" 
                                    style="width: {{ $percent }}%"
                                ></div>
                            </div>
                        </div>
                    @endif

                    <!-- Items Card List -->
                    @php
                        $variantsMap = $this->productVariantsMap;
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs divide-y divide-slate-100 overflow-hidden">
                        @foreach ($items as $index => $item)
                            @php
                                $itemVariants = $variantsMap[$item['id']] ?? [];
                            @endphp
                            <div wire:key="cart-row-{{ $item['id'] }}-{{ $item['variant_id'] ?? 0 }}-{{ $index }}" class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group hover:bg-slate-50/50 transition">
                                
                                <!-- Product Thumb & Details -->
                                <div class="flex items-start gap-3.5 flex-1">
                                    <a href="{{ route('product.detail', $item['slug']) }}" wire:navigate.hover class="w-20 h-20 rounded-xl bg-[#fbfdfc] border border-slate-100 p-2 shrink-0 overflow-hidden block">
                                        <img 
                                            src="{{ $item['image'] }}" 
                                            alt="{{ $item['name'] }}" 
                                            class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-200"
                                        >
                                    </a>

                                    <div class="space-y-1">
                                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-[#CE171F] bg-red-50 px-2 py-0.5 rounded">
                                            {{ $item['brand'] }}
                                        </span>
                                        <h3 class="text-xs sm:text-sm font-semibold text-slate-800 leading-snug line-clamp-2">
                                            <a href="{{ route('product.detail', $item['slug']) }}" wire:navigate.hover class="hover:text-[#CE171F] transition-colors">
                                                {{ $item['name'] }}
                                            </a>
                                        </h3>
                                        @if (count($itemVariants) > 0)
                                            <div class="pt-0.5 flex items-center gap-2 flex-wrap">
                                                <span class="text-[11px] text-slate-500 font-medium">Phân loại:</span>
                                                <select
                                                    wire:change="changeItemVariant({{ $index }}, $event.target.value)"
                                                    class="text-[11px] font-semibold text-slate-800 bg-slate-50 border border-slate-200 hover:border-[#CE171F] rounded-lg px-2 py-1 focus:outline-none focus:border-[#CE171F] cursor-pointer"
                                                >
                                                    @foreach ($itemVariants as $varOpt)
                                                        <option value="{{ $varOpt['id'] }}" @selected((int) ($item['variant_id'] ?? 0) === (int) $varOpt['id'])>
                                                            {{ $varOpt['label'] }} ({{ number_format($varOpt['price'], 0, ',', '.') }}đ)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @else
                                            <div class="text-[11px] text-slate-400 font-normal">
                                                Quy cách: {{ $item['packing'] }}
                                            </div>
                                        @endif
                                        <div class="sm:hidden text-xs font-bold text-[#CE171F] pt-1">
                                            {{ number_format($item['price'], 0, ',', '.') }}đ
                                        </div>
                                    </div>
                                </div>

                                <!-- Quantity Stepper & Price Column -->
                                <div class="flex items-center justify-between sm:justify-end gap-6 sm:gap-8 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                    <!-- Stepper -->
                                    <div class="inline-flex items-center bg-white border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                                        <button 
                                            wire:click="decrementItem({{ $index }})" 
                                            type="button" 
                                            class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition cursor-pointer"
                                            title="Giảm số lượng"
                                        >
                                            <i class="fa-solid fa-minus text-[10px]"></i>
                                        </button>
                                        <span class="w-9 text-center text-xs font-bold text-slate-800">
                                            {{ $item['qty'] }}
                                        </span>
                                        <button 
                                            wire:click="incrementItem({{ $index }})" 
                                            type="button" 
                                            class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition cursor-pointer"
                                            title="Tăng số lượng"
                                        >
                                            <i class="fa-solid fa-plus text-[10px]"></i>
                                        </button>
                                    </div>

                                    <!-- Subtotal -->
                                    <div class="text-right min-w-[90px]">
                                        <div class="text-sm font-bold text-[#CE171F]">
                                            {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}đ
                                        </div>
                                        <div class="hidden sm:block text-[10px] text-slate-400 font-normal">
                                            {{ number_format($item['price'], 0, ',', '.') }}đ / sp
                                        </div>
                                    </div>

                                    <!-- Remove Button -->
                                    <button 
                                        wire:click="removeItem({{ $index }})" 
                                        type="button" 
                                        class="w-8 h-8 rounded-lg text-slate-300 hover:text-rose-500 hover:bg-rose-50 flex items-center justify-center transition cursor-pointer"
                                        title="Xóa sản phẩm"
                                    >
                                        <i class="fa-regular fa-trash-can text-xs"></i>
                                    </button>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <!-- Bottom Nav Link -->
                    <div class="pt-2">
                        <a 
                            href="{{ route('category') }}" 
                            wire:navigate.hover 
                            class="inline-flex items-center gap-2 text-xs font-semibold text-[#CE171F] hover:underline"
                        >
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                            <span>Tiếp tục chọn thêm sản phẩm khác</span>
                        </a>
                    </div>

                </div>

                <!-- Right Column: Summary Card (4 cols) -->
                <div class="lg:col-span-4 space-y-4 lg:sticky lg:top-24">

                    <!-- Voucher Card -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs space-y-3">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-ticket text-amber-500"></i>
                            <span>Mã giảm giá / Ưu đãi</span>
                        </label>

                        @if ($appliedVoucher)
                            <div class="p-3 rounded-xl bg-red-50 border border-red-200 flex items-center gap-3">
                                @if (!empty($appliedCouponData['image_url']))
                                    <img src="{{ curator_url($appliedCouponData['image_url']) ?: asset($appliedCouponData['image_url']) }}" alt="{{ $appliedCouponData['title'] ?? '' }}" class="w-11 h-11 object-contain rounded-lg bg-white p-1 border border-red-100 shadow-2xs shrink-0">
                                @endif
                                <div class="flex-1 min-w-0 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5 text-xs text-emerald-900 font-bold">
                                            <i class="fa-solid fa-circle-check text-[#CE171F]"></i>
                                            <span>Đã áp dụng: {{ $appliedVoucher }}</span>
                                        </div>
                                        <button 
                                            wire:click="removeVoucher" 
                                            type="button" 
                                            class="text-xs text-slate-400 hover:text-rose-600 transition cursor-pointer flex items-center gap-1"
                                            title="Hủy mã ưu đãi"
                                        >
                                            <i class="fa-solid fa-trash-can text-[11px]"></i>
                                            <span class="text-[11px] font-semibold">Hủy</span>
                                        </button>
                                    </div>
                                    <div class="text-[11px] text-emerald-800 font-medium flex items-center justify-between">
                                        <span class="truncate pr-2">{{ $appliedCouponData['title'] ?? 'Mã ưu đãi' }}</span>
                                        @if ($voucherDiscount > 0)
                                            <span class="font-bold text-[#991B1B] shrink-0">-{{ number_format($voucherDiscount, 0, ',', '.') }}đ</span>
                                        @elseif ($appliedCouponData && ($appliedCouponData['type'] === 'shipping' || str_starts_with($appliedVoucher, 'GIAMSHIP') || in_array($appliedVoucher, ['FREESHIP', 'FREESHIP25', 'FREESHIP40', 'SHIP25'])))
                                            <span class="font-bold text-[#991B1B] shrink-0">{{ $this->shippingFee === 0 ? 'Miễn phí ship' : 'Giảm ship ' . number_format($appliedCouponData['value'] ?? 0, 0, ',', '.') . 'đ' }}</span>
                                        @elseif ($appliedCouponData && $appliedCouponData['type'] === 'gift')
                                            <span class="font-bold text-amber-700 shrink-0">🎁 Quà tặng kèm</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                <input 
                                    wire:model="voucherCode" 
                                    wire:keydown.enter="applyVoucher" 
                                    type="text" 
                                    placeholder="Nhập mã (FREESHIP, GIAM50K, GIAM20K...)" 
                                    class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2.5 uppercase font-medium focus:border-[#CE171F] focus:outline-none"
                                >
                                <button 
                                    wire:click="applyVoucher" 
                                    type="button" 
                                    class="px-4 py-2.5 bg-[#CE171F] hover:bg-[#B91C1C] text-white text-xs font-semibold rounded-xl transition shrink-0 cursor-pointer"
                                >
                                    Áp dụng
                                </button>
                            </div>
                            @if ($voucherError)
                                <div class="text-[11px] text-rose-600 font-medium">
                                    {{ $voucherError }}
                                </div>
                            @endif

                            <!-- Quick Available Coupon Badges -->
                            @if (!empty($availableCoupons) && count($availableCoupons) > 0)
                                <div class="pt-2 space-y-1.5">
                                    <div class="text-[11px] font-medium text-slate-500 flex items-center gap-1">
                                        <i class="fa-solid fa-gift text-amber-500 text-[10px]"></i>
                                        <span>Mã ưu đãi dành cho bạn:</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @foreach ($availableCoupons as $c)
                                            <button 
                                                wire:click="applyVoucher('{{ $c->code }}')" 
                                                type="button" 
                                                class="text-[10px] bg-red-50/80 hover:bg-[#CE171F] hover:text-white text-slate-800 border border-red-200/90 font-semibold px-2 py-1 rounded-lg transition cursor-pointer flex items-center gap-1.5 shadow-2xs group"
                                                title="{{ $c->title }} - Đơn từ {{ number_format($c->min_order_amount, 0, ',', '.') }}đ"
                                            >
                                                @if (!empty($c->image_url))
                                                    <img src="{{ curator_url($c->image_url) ?: asset($c->image_url) }}" alt="{{ $c->title }}" class="w-4 h-4 object-contain rounded-xs">
                                                @endif
                                                <span class="text-[#CE171F] group-hover:text-white font-bold">{{ $c->code }}</span>
                                                <span class="opacity-75 font-normal">({{ $c->badge }})</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Payment Summary Card -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-xs space-y-4">
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-3 border-b border-slate-100">
                            Tóm tắt đơn hàng
                        </h2>

                        <div class="space-y-2.5 text-xs">
                            <div class="flex items-center justify-between text-slate-600 font-normal">
                                <span>Tạm tính ({{ count($items) }} sản phẩm):</span>
                                <span class="font-semibold text-slate-800">{{ number_format($this->subtotal, 0, ',', '.') }}đ</span>
                            </div>

                            @if ($voucherDiscount > 0)
                                <div class="flex items-center justify-between text-[#991B1B] font-medium">
                                    <span>Giảm giá ưu đãi:</span>
                                    <span>-{{ number_format($voucherDiscount, 0, ',', '.') }}đ</span>
                                </div>
                            @endif

                            @if ($appliedCouponData && $appliedCouponData['type'] === 'gift')
                                <div class="flex items-center justify-between text-amber-700 font-medium bg-amber-50/60 p-1.5 rounded-lg border border-amber-100">
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-gift text-amber-500"></i> Quà tặng:
                                    </span>
                                    <span class="font-bold">{{ $appliedCouponData['title'] }}</span>
                                </div>
                            @endif

                            <div class="flex items-center justify-between text-slate-600 font-normal">
                                <span>Phí vận chuyển hỏa tốc 1h:</span>
                                @if ($this->shippingFee === 0)
                                    <span class="font-bold text-[#CE171F] uppercase">Miễn phí</span>
                                @else
                                    <span class="font-semibold text-slate-800">{{ number_format($this->shippingFee, 0, ',', '.') }}đ</span>
                                @endif
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-baseline justify-between">
                                <span class="text-sm font-bold text-slate-900">Tổng thanh toán:</span>
                                <div class="text-right">
                                    <div class="text-xl font-bold text-[#CE171F] tracking-tight">
                                        {{ number_format($this->total, 0, ',', '.') }}đ
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-normal">Đã bao gồm thuế VAT</div>
                                </div>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <a 
                            href="{{ route('checkout') }}" 
                            wire:navigate.hover 
                            class="w-full py-3.5 px-4 rounded-xl bg-[#CE171F] hover:bg-[#3d6b4d] text-white text-xs sm:text-sm font-semibold flex items-center justify-center gap-2 transition-all shadow-md shadow-[#4d835f]/25 text-center block"
                        >
                            <span>Tiến hành đặt hàng</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>

                        <!-- Service Guarantees / Trust Badges -->
                        @if(!empty($trustBadges) && is_array($trustBadges) && count($trustBadges) > 0)
                        <div class="pt-3 border-t border-slate-100 space-y-2 text-[11px] text-slate-500 font-normal">
                            @foreach($trustBadges as $badge)
                            <div class="flex items-start gap-2">
                                @if(!empty($badge['icon']))
                                <i class="{{ $badge['icon'] }} text-[#CE171F] w-4 mt-0.5 shrink-0"></i>
                                @endif
                                <div class="leading-relaxed">
                                    @if(!empty($badge['title']))
                                    <strong class="text-slate-700 font-semibold">{{ $badge['title'] }}:</strong>
                                    @endif
                                    @if(!empty($badge['description']))
                                    <span>{{ $badge['description'] }}</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                </div>

            </div>

        <!-- Empty Cart State -->
        @else
            <div class="bg-white rounded-3xl p-10 sm:p-16 border border-slate-100 text-center max-w-lg mx-auto shadow-xs space-y-4 my-8">
                <div class="w-20 h-20 rounded-full bg-red-50 text-[#CE171F] flex items-center justify-center mx-auto text-3xl">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                @if(!empty($cart['empty_title']))
                <h3 class="text-base sm:text-lg font-bold text-slate-800">
                    {{ $cart['empty_title'] }}
                </h3>
                @endif
                @if(!empty($cart['empty_description']))
                <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                    {{ $cart['empty_description'] }}
                </p>
                @endif
                @if(!empty($cart['empty_btn_text']))
                <div class="pt-2">
                    <a 
                        href="{{ $cart['empty_btn_link'] ?? route('category') }}" 
                        wire:navigate.hover 
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#CE171F] hover:bg-[#3d6b4d] text-white text-xs font-semibold shadow-sm transition-all"
                    >
                        <i class="fa-solid fa-basket-shopping text-xs"></i>
                        <span>{{ $cart['empty_btn_text'] }}</span>
                    </a>
                </div>
                @endif
            </div>
        @endif

    </div>
</div>
