<div class="min-h-screen bg-[#f8faf9] pt-2.5 pb-8 sm:py-8 selection:bg-[#CE171F] selection:text-white">
    <!-- Page SEO Meta Tags -->
    <livewire:seo.checkout />

    @php
        $checkout = $setting?->interface_settings['checkout'] ?? [];
        $checkoutTitle = !empty($checkout['title']) ? $checkout['title'] : 'Đặt hàng & Thanh toán';
    @endphp

    <!-- Breadcrumb Navigation -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-3 sm:mb-6">
        <nav class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-slate-500 font-normal whitespace-nowrap overflow-hidden">
            <a href="{{ route('home') }}" wire:navigate.hover class="hover:text-[#CE171F] transition-colors shrink-0">Trang chủ</a>
            <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-300 shrink-0"></i>
            <a href="{{ route('cart') }}" wire:navigate.hover class="hover:text-[#CE171F] transition-colors shrink-0">Giỏ hàng</a>
            <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-300 shrink-0"></i>
            <span class="text-slate-800 font-medium sm:font-semibold min-w-0 truncate">{{ $checkoutTitle }}</span>
        </nav>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="pb-3 mb-4 sm:pb-4 sm:mb-6 border-b border-slate-200/80">
            <h1 class="text-[15px] sm:text-xl md:text-2xl font-bold text-[#991B1B] sm:text-slate-900 tracking-tight leading-snug">
                {{ $checkoutTitle }}
            </h1>
            @if(!empty($checkout['subtitle']))
            <p class="text-xs text-slate-500 font-normal mt-1">
                {{ $checkout['subtitle'] }}
            </p>
            @endif
            @if(!empty($checkout['shipping_note']))
            <div class="mt-2.5 inline-flex items-center gap-2 text-xs text-emerald-800 bg-red-50 px-3 py-1.5 rounded-xl border border-red-200/60 font-medium">
                <i class="fa-solid fa-bolt text-amber-500 text-xs"></i>
                <span>{{ $checkout['shipping_note'] }}</span>
            </div>
            @endif
        </div>

        @if ($errorMessage)
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2.5">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-base shrink-0"></i>
                <span>{{ $errorMessage }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

            <!-- Left Column: Checkout Forms (7 cols) -->
            <div class="lg:col-span-7 space-y-6">

                <!-- 1. Customer Info & Shipping Address -->
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-100 shadow-xs space-y-4">
                    <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-slate-100">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-[#CE171F] flex items-center justify-center text-xs font-bold">1</span>
                        <span>Thông tin người nhận & Địa chỉ giao hàng</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Customer Name -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Họ và tên người nhận <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                wire:model="customer_name" 
                                type="text" 
                                placeholder="Ví dụ: Nguyễn Văn A" 
                                class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-[#CE171F] focus:outline-none bg-slate-50/50"
                            >
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Số điện thoại nhận hàng <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                wire:model="customer_phone" 
                                type="tel" 
                                placeholder="0988 888 888" 
                                class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-[#CE171F] focus:outline-none bg-slate-50/50"
                            >
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Email (Nhận thông báo đơn)
                            </label>
                            <input 
                                wire:model="customer_email" 
                                type="email" 
                                placeholder="email@example.com" 
                                class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-[#CE171F] focus:outline-none bg-slate-50/50"
                            >
                        </div>

                        <!-- Tỉnh / Thành phố (34 Tỉnh/TP sau sáp nhập - Có ô Tìm kiếm nhanh) -->
                        <div 
                            class="relative"
                            x-data="{
                                open: false,
                                search: '',
                                items: @js($provinces),
                                selectedId: @entangle('province_id').live,
                                norm(s) {
                                    return (s || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[đĐ]/g, 'd').toLowerCase().trim();
                                },
                                get selectedLabel() {
                                    const found = this.items.find(i => String(i.id) === String(this.selectedId));
                                    return found ? found.name : '-- Chọn Tỉnh / Thành phố --';
                                },
                                get filteredItems() {
                                    const q = this.norm(this.search);
                                    if (!q) return this.items;
                                    return this.items.filter(i => this.norm(i.name).includes(q));
                                },
                                selectItem(item) {
                                    this.selectedId = String(item.id);
                                    this.open = false;
                                    this.search = '';
                                }
                            }"
                            @click.outside="open = false"
                        >
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Tỉnh / Thành phố <span class="text-rose-500">*</span>
                            </label>
                            <button 
                                type="button"
                                @click="open = !open; if (open) $nextTick(() => $refs.provinceSearchInput.focus())"
                                class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-[#CE171F] focus:outline-none bg-white cursor-pointer flex items-center justify-between text-left transition hover:border-slate-300"
                            >
                                <span class="truncate font-medium text-slate-800" x-text="selectedLabel"></span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-150 shrink-0 ml-2" :class="open ? 'rotate-180 text-[#CE171F]' : ''"></i>
                            </button>

                            <div 
                                x-show="open"
                                x-transition.opacity.duration.150ms
                                style="display: none;"
                                class="absolute left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden"
                            >
                                <!-- Ô tìm kiếm Tỉnh / Thành phố -->
                                <div class="p-2 border-b border-slate-100 bg-slate-50/70">
                                    <div class="relative flex items-center">
                                        <i class="fa-solid fa-magnifying-glass text-[11px] text-slate-400 absolute left-3 pointer-events-none"></i>
                                        <input 
                                            x-ref="provinceSearchInput"
                                            x-model="search"
                                            @keydown.escape.stop="open = false"
                                            type="text" 
                                            placeholder="Gõ tên Tỉnh / Thành phố để tìm..." 
                                            class="w-full pl-8 pr-7 py-1.5 text-xs bg-white border border-slate-200 rounded-xl focus:border-[#CE171F] focus:outline-none text-slate-800"
                                        >
                                        <button 
                                            x-show="search.length > 0"
                                            @click="search = ''; $refs.provinceSearchInput.focus()"
                                            type="button"
                                            class="absolute right-2.5 text-slate-400 hover:text-slate-600 text-xs cursor-pointer"
                                        >
                                            <i class="fa-solid fa-circle-xmark"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Danh sách Tỉnh / Thành phố -->
                                <ul class="max-h-60 overflow-y-auto divide-y divide-slate-50 py-1 text-xs">
                                    <template x-for="item in filteredItems" :key="item.id">
                                        <li>
                                            <button 
                                                type="button"
                                                @click="selectItem(item)"
                                                class="w-full px-3.5 py-2 text-left flex items-center justify-between hover:bg-red-50/70 transition cursor-pointer"
                                                :class="String(selectedId) === String(item.id) ? 'bg-red-50 text-[#CE171F] font-bold' : 'text-slate-700 font-normal'"
                                            >
                                                <span class="truncate" x-text="item.name"></span>
                                                <i x-show="String(selectedId) === String(item.id)" class="fa-solid fa-check text-[10px] text-[#CE171F] shrink-0 ml-2"></i>
                                            </button>
                                        </li>
                                    </template>
                                    <li x-show="filteredItems.length === 0" class="px-3.5 py-3 text-center text-slate-400 text-xs">
                                        Không tìm thấy Tỉnh / Thành phố phù hợp
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Phường / Xã / Đặc khu (Mô hình 2 cấp sau sáp nhập - Có ô Tìm kiếm nhanh) -->
                        <div 
                            class="relative"
                            wire:key="ward-select-box-{{ $province_id }}"
                            x-data="{
                                open: false,
                                search: '',
                                items: @js($wards),
                                selectedCode: @entangle('ward_code').live,
                                norm(s) {
                                    return (s || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[đĐ]/g, 'd').toLowerCase().trim();
                                },
                                get selectedLabel() {
                                    if (this.selectedCode === '__custom__') {
                                        return '-- Nhập Phường / Xã khác --';
                                    }
                                    const found = this.items.find(i => String(i.code) === String(this.selectedCode));
                                    return found ? found.name : '-- Chọn Phường / Xã --';
                                },
                                get filteredItems() {
                                    const q = this.norm(this.search);
                                    if (!q) return this.items;
                                    return this.items.filter(i => this.norm(i.name).includes(q));
                                },
                                selectItem(item) {
                                    this.selectedCode = String(item.code);
                                    this.open = false;
                                    this.search = '';
                                },
                                selectCustom(prefill = '') {
                                    this.selectedCode = '__custom__';
                                    if (prefill) {
                                        $wire.set('custom_ward', prefill);
                                    }
                                    this.open = false;
                                    this.search = '';
                                }
                            }"
                            @click.outside="open = false"
                        >
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Phường / Xã / Đặc khu <span class="text-rose-500">*</span>
                            </label>
                            <button 
                                type="button"
                                @click="open = !open; if (open) $nextTick(() => $refs.wardSearchInput.focus())"
                                class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-[#CE171F] focus:outline-none bg-white cursor-pointer flex items-center justify-between text-left transition hover:border-slate-300"
                            >
                                <span class="truncate font-medium text-slate-800" x-text="selectedLabel"></span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-150 shrink-0 ml-2" :class="open ? 'rotate-180 text-[#CE171F]' : ''"></i>
                            </button>

                            <div 
                                x-show="open"
                                x-transition.opacity.duration.150ms
                                style="display: none;"
                                class="absolute left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden"
                            >
                                <!-- Ô tìm kiếm Phường / Xã -->
                                <div class="p-2 border-b border-slate-100 bg-slate-50/70">
                                    <div class="relative flex items-center">
                                        <i class="fa-solid fa-magnifying-glass text-[11px] text-slate-400 absolute left-3 pointer-events-none"></i>
                                        <input 
                                            x-ref="wardSearchInput"
                                            x-model="search"
                                            @keydown.escape.stop="open = false"
                                            type="text" 
                                            placeholder="Gõ tên Phường / Xã để tìm nhanh..." 
                                            class="w-full pl-8 pr-7 py-1.5 text-xs bg-white border border-slate-200 rounded-xl focus:border-[#CE171F] focus:outline-none text-slate-800"
                                        >
                                        <button 
                                            x-show="search.length > 0"
                                            @click="search = ''; $refs.wardSearchInput.focus()"
                                            type="button"
                                            class="absolute right-2.5 text-slate-400 hover:text-slate-600 text-xs cursor-pointer"
                                        >
                                            <i class="fa-solid fa-circle-xmark"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Danh sách Phường / Xã -->
                                <ul class="max-h-60 overflow-y-auto divide-y divide-slate-50 py-1 text-xs">
                                    <template x-for="item in filteredItems" :key="item.code">
                                        <li>
                                            <button 
                                                type="button"
                                                @click="selectItem(item)"
                                                class="w-full px-3.5 py-2 text-left flex items-center justify-between hover:bg-red-50/70 transition cursor-pointer"
                                                :class="String(selectedCode) === String(item.code) ? 'bg-red-50 text-[#CE171F] font-bold' : 'text-slate-700 font-normal'"
                                            >
                                                <span class="truncate" x-text="item.name"></span>
                                                <i x-show="String(selectedCode) === String(item.code)" class="fa-solid fa-check text-[10px] text-[#CE171F] shrink-0 ml-2"></i>
                                            </button>
                                        </li>
                                    </template>
                                    <li class="border-t border-slate-100">
                                        <button 
                                            type="button"
                                            @click="selectCustom(search.trim())"
                                            class="w-full px-3.5 py-2 text-left flex items-center justify-between hover:bg-amber-50 text-amber-700 font-semibold transition cursor-pointer"
                                        >
                                            <span x-text="search.trim().length > 0 ? 'Sử dụng tên vừa nhập: &quot;' + search.trim() + '&quot;' : '-- Nhập Phường / Xã khác --'"></span>
                                            <i class="fa-solid fa-pen text-[10px] shrink-0 ml-2"></i>
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            @if ($ward_code === '__custom__')
                                <input 
                                    wire:model.live="custom_ward" 
                                    type="text" 
                                    placeholder="Nhập tên Phường / Xã / Đặc khu của bạn..." 
                                    class="w-full mt-2 text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-[#CE171F] focus:outline-none bg-white"
                                >
                            @endif
                        </div>

                        <!-- Specific Address -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Địa chỉ cụ thể (Số nhà, tên đường, tòa nhà...) <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                wire:model="shipping_address" 
                                type="text" 
                                placeholder="Ví dụ: 128 Hai Bà Trưng, Tòa nhà Bitexco..." 
                                class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-[#CE171F] focus:outline-none bg-slate-50/50"
                            >
                        </div>

                        <!-- Order Notes -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Ghi chú cho shipper / cửa hàng</label>
                            <textarea 
                                wire:model="order_notes" 
                                rows="2" 
                                placeholder="{{ !empty($checkout['note_placeholder']) ? $checkout['note_placeholder'] : 'Ví dụ: Gọi trước khi đến 15 phút, gửi bảo vệ nếu vắng mặt...' }}" 
                                class="w-full text-xs border border-slate-200 rounded-xl p-3 focus:border-[#CE171F] focus:outline-none bg-slate-50/50"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- 2. Payment Method (COD) -->
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-100 shadow-xs space-y-4">
                    <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-slate-100">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-[#CE171F] flex items-center justify-center text-xs font-bold">2</span>
                        <span>Phương thức thanh toán</span>
                    </h2>

                    <!-- COD -->
                    <div class="p-4 rounded-xl border-2 border-[#CE171F] bg-red-50/40 ring-1 ring-[#4d835f]/20 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-[#CE171F] flex items-center justify-center text-base shrink-0">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                    <span>{{ !empty($checkout['cod_title']) ? $checkout['cod_title'] : 'Thanh toán khi nhận hàng (COD)' }}</span>
                                    <span class="text-[10px] bg-emerald-100 text-[#CE171F] font-bold px-2 py-0.5 rounded-full">Mặc định</span>
                                </div>
                                @if(!empty($checkout['cod_desc']))
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ $checkout['cod_desc'] }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="w-6 h-6 rounded-full bg-[#CE171F] text-white flex items-center justify-center text-xs shrink-0 shadow-xs">
                            <i class="fa-solid fa-check"></i>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Sticky Order Summary (5 cols) -->
            <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-24">
                
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-100 shadow-xs space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Đơn hàng của bạn
                        </h2>
                        <a href="{{ route('cart') }}" wire:navigate.hover class="text-xs text-[#CE171F] font-semibold hover:underline">
                            Sửa giỏ hàng
                        </a>
                    </div>

                    <!-- Products Mini List (Fix Quantity Badge Clipping) -->
                    <div class="space-y-3.5 max-h-72 overflow-y-auto pt-2.5 pb-1 px-1">
                        @foreach ($items as $p)
                            <div class="flex items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="relative shrink-0">
                                        <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200/80 p-1 flex items-center justify-center">
                                            <img src="{{ $p['image'] ?? asset('storage/logo/favicon-dnv.png') }}" alt="{{ $p['name'] ?? '' }}" class="w-full h-full object-contain">
                                        </div>
                                        <span class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-[#CE171F] text-white text-[9.5px] font-bold flex items-center justify-center border-2 border-white shadow-xs z-10">
                                            {{ $p['qty'] }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-slate-800 truncate">{{ $p['name'] ?? '' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $p['brand'] ?? 'DNV' }} • {{ $p['packing'] ?? '' }}</div>
                                    </div>
                                </div>
                                <span class="font-bold text-slate-800 whitespace-nowrap">
                                    {{ number_format(($p['price'] ?? 0) * ($p['qty'] ?? 1), 0, ',', '.') }}đ
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Voucher Input & Quick Selector -->
                    <div class="pt-3 border-t border-slate-100 space-y-2.5">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-700">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-ticket text-[#CE171F]"></i>
                                <span>Mã giảm giá / Ưu đãi</span>
                            </span>
                            @if ($appliedVoucher)
                                <button 
                                    wire:click="removeVoucher" 
                                    type="button" 
                                    class="text-xs text-rose-600 hover:text-rose-700 transition font-medium cursor-pointer"
                                >
                                    Đổi mã khác
                                </button>
                            @endif
                        </div>

                        @if ($appliedVoucher)
                            <div class="p-3 rounded-xl bg-red-50/90 border border-red-200 flex items-center gap-3">
                                @if (!empty($appliedCouponData['image_url']))
                                    <img src="{{ curator_url($appliedCouponData['image_url']) ?: asset($appliedCouponData['image_url']) }}" alt="{{ $appliedCouponData['title'] ?? '' }}" class="w-10 h-10 object-contain rounded-lg bg-white p-1 border border-red-100 shadow-2xs shrink-0">
                                @endif
                                <div class="flex-1 min-w-0 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="bg-[#CE171F] text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase">
                                                {{ $appliedVoucher }}
                                            </span>
                                            <span class="text-xs font-bold text-emerald-900 truncate">
                                                {{ $appliedCouponData['title'] ?? 'Ưu đãi thành công' }}
                                            </span>
                                        </div>
                                        <button 
                                            wire:click="removeVoucher" 
                                            type="button" 
                                            class="text-slate-400 hover:text-rose-600 transition cursor-pointer text-xs p-1"
                                            title="Hủy mã ưu đãi"
                                        >
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div class="text-[11px] text-[#991B1B] font-medium flex items-center justify-between">
                                        <span class="truncate pr-2">{{ $appliedCouponData['description'] ?? 'Áp dụng cho đơn hàng này' }}</span>
                                        @if ($voucherDiscount > 0)
                                            <span class="font-bold text-emerald-800 shrink-0">-{{ number_format($voucherDiscount, 0, ',', '.') }}đ</span>
                                        @elseif ($appliedCouponData && ($appliedCouponData['type'] === 'shipping' || str_starts_with($appliedVoucher, 'GIAMSHIP') || in_array($appliedVoucher, ['FREESHIP', 'FREESHIP25', 'FREESHIP40', 'SHIP25'])))
                                            <span class="font-bold text-emerald-800 shrink-0">{{ $this->shippingFee === 0 ? 'Miễn phí ship' : 'Giảm ship ' . number_format($appliedCouponData['value'] ?? 0, 0, ',', '.') . 'đ' }}</span>
                                        @elseif ($appliedCouponData && $appliedCouponData['type'] === 'gift')
                                            <span class="font-bold text-amber-700 shrink-0">🎁 Tặng kèm quà</span>
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
                                    class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 uppercase font-medium focus:border-[#CE171F] focus:outline-none"
                                >
                                <button 
                                    wire:click="applyVoucher" 
                                    type="button" 
                                    class="px-3.5 py-2 bg-[#CE171F] hover:bg-[#3d6b4d] text-white text-xs font-semibold rounded-xl transition shrink-0 cursor-pointer shadow-2xs"
                                >
                                    Áp dụng
                                </button>
                            </div>

                            @if ($errorMessage && !str_contains($errorMessage, 'Vui lòng'))
                                <div class="text-[11.5px] text-rose-600 font-medium flex items-center gap-1.5 bg-rose-50 px-2.5 py-1.5 rounded-lg border border-rose-100">
                                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-xs"></i>
                                    <span>{{ $errorMessage }}</span>
                                </div>
                            @endif

                            <!-- Available Coupons Quick List -->
                            @if (!empty($availableCoupons) && count($availableCoupons) > 0)
                                <div class="pt-1 space-y-1.5">
                                    <div class="text-[10.5px] font-medium text-slate-500 flex items-center justify-between">
                                        <span>Gợi ý ưu đãi cho đơn hàng:</span>
                                    </div>
                                    <div class="space-y-1.5 max-h-[160px] overflow-y-auto pr-1 [&::-webkit-scrollbar]:w-1 [&::-webkit-scrollbar-thumb]:bg-slate-200">
                                        @foreach ($availableCoupons as $c)
                                            @php
                                                $isEligible = $this->subtotal >= ($c->min_order_amount ?? 0);
                                            @endphp
                                            <div class="flex items-center justify-between p-2 rounded-xl border {{ $isEligible ? 'border-red-200/80 bg-red-50/40 hover:bg-red-50' : 'border-slate-100 bg-slate-50/60 opacity-75' }} transition gap-2">
                                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                                    @if (!empty($c->image_url))
                                                        <img src="{{ curator_url($c->image_url) ?: asset($c->image_url) }}" alt="{{ $c->title }}" class="w-8 h-8 object-contain rounded-md bg-white p-0.5 border border-slate-200/60 shrink-0">
                                                    @endif
                                                    <div class="min-w-0 pr-1">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="text-[9.5px] font-bold uppercase px-1.5 py-0.5 rounded {{ $isEligible ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                                                {{ $c->code }}
                                                            </span>
                                                            <span class="text-[11px] font-semibold text-slate-800 truncate">
                                                                {{ $c->title }}
                                                            </span>
                                                        </div>
                                                        <div class="text-[10px] text-slate-400 mt-0.5 truncate">
                                                            Đơn từ {{ number_format($c->min_order_amount, 0, ',', '.') }}đ
                                                            @if (!$isEligible)
                                                                <span class="text-rose-500 font-medium">• Mua thêm {{ number_format($c->min_order_amount - $this->subtotal, 0, ',', '.') }}đ</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <button 
                                                    wire:click="applyVoucher('{{ $c->code }}')" 
                                                    type="button" 
                                                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition shrink-0 cursor-pointer {{ $isEligible ? 'bg-[#CE171F] text-white hover:bg-[#3d6b4d]' : 'bg-slate-200 text-slate-500 hover:bg-slate-300' }}"
                                                >
                                                    Áp dụng
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Price Calculations -->
                    <div class="pt-3 border-t border-slate-100 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between text-slate-600 font-normal">
                            <span>Tạm tính hàng:</span>
                            <span class="font-semibold text-slate-800">{{ number_format($this->subtotal, 0, ',', '.') }}đ</span>
                        </div>

                        @if ($voucherDiscount > 0)
                            <div class="flex items-center justify-between text-[#991B1B] font-medium">
                                <span>Giảm giá voucher:</span>
                                <span>-{{ number_format($voucherDiscount, 0, ',', '.') }}đ</span>
                            </div>
                        @endif

                        @if ($appliedCouponData && $appliedCouponData['type'] === 'gift')
                            <div class="flex items-center justify-between text-amber-700 font-medium bg-amber-50/80 p-2 rounded-xl border border-amber-200/70">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-gift text-amber-500"></i>
                                    <span>Quà tặng đính kèm:</span>
                                </span>
                                <span class="font-bold text-amber-900">{{ $appliedCouponData['title'] }}</span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between text-slate-600 font-normal">
                            <span>Phí vận chuyển:</span>
                            @if ($this->shippingFee === 0)
                                <span class="font-bold text-[#CE171F] uppercase">Miễn phí</span>
                            @else
                                <span class="font-semibold text-slate-800">{{ number_format($this->shippingFee, 0, ',', '.') }}đ</span>
                            @endif
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-baseline justify-between">
                            <span class="text-sm font-bold text-slate-900">Tổng thanh toán:</span>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-[#CE171F] tracking-tight">
                                    {{ number_format($this->total, 0, ',', '.') }}đ
                                </div>
                                <div class="text-[10px] text-slate-400 font-normal">Đã bao gồm thuế VAT</div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Order Button -->
                    <button 
                        wire:click="submitOrder" 
                        wire:loading.attr="disabled"
                        type="button" 
                        class="w-full py-4 px-6 rounded-2xl bg-[#CE171F] hover:bg-[#3d6b4d] text-white text-sm font-bold flex items-center justify-center gap-2 transition-all shadow-lg shadow-[#4d835f]/25 cursor-pointer disabled:opacity-50"
                    >
                        <span wire:loading.remove>{{ !empty($checkout['submit_btn_text']) ? $checkout['submit_btn_text'] : 'Xác Nhận Đặt Hàng →' }}</span>
                        <span wire:loading class="flex items-center gap-2">
                            <i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý đơn...
                        </span>
                    </button>

                    <!-- Trust Note -->
                    <p class="text-[11px] text-center text-slate-400 font-normal leading-relaxed">
                        Bằng việc bấm đặt hàng, bạn đồng ý với các điều khoản mua sắm và chính sách bảo vệ dữ liệu của Dự Án ASXH
                    </p>
                </div>

            </div>

        </div>

    </div>
</div>
